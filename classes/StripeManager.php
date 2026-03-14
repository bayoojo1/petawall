<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

class StripeManager {

    private $db;
    private $priceToRole;
    private $planToPrice;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->loadConfig();
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    }

    private function loadConfig() {

        if (!defined('STRIPE_SECRET_KEY')) {
            throw new Exception('Stripe configuration missing.');
        }

        global $stripePriceToRole, $stripePlanToPrice;

        $this->priceToRole = $stripePriceToRole ?? [
            'price_1Slv7hReUJdbdkCUWse4Cddp' => 2,
            'price_1Slv9KReUJdbdkCUba1o3mIj' => 3
        ];

        $this->planToPrice = $stripePlanToPrice ?? [
            'basic' => 'price_1Slv7hReUJdbdkCUWse4Cddp',
            'premium' => 'price_1Slv9KReUJdbdkCUba1o3mIj'
        ];
    }

    /* ==========================================================
       WEBHOOK IDEMPOTENCY
    ========================================================== */

    private function isEventAlreadyProcessed($eventId) {
        $stmt = $this->db->prepare("SELECT processed FROM stripe_webhook_events WHERE stripe_event_id = ?");
        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function logEvent($eventId, $eventType) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO stripe_webhook_events
            (stripe_event_id, event_type)
            VALUES (?, ?)
        ");
        $stmt->execute([$eventId, $eventType]);
    }

    private function markEventProcessed($eventId) {
        $stmt = $this->db->prepare("
            UPDATE stripe_webhook_events
            SET processed = 1, processed_at = NOW()
            WHERE stripe_event_id = ?
        ");
        $stmt->execute([$eventId]);
    }

    /* ==========================================================
       WEBHOOK HANDLER
    ========================================================== */

    public function handleWebhook($payload, $sigHeader) {

        if (!defined('STRIPE_WEBHOOK_SECRET')) {
            http_response_code(500);
            return;
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                STRIPE_WEBHOOK_SECRET
            );
        } catch (\Exception $e) {
            http_response_code(400);
            return;
        }

        // Idempotency check
        $existing = $this->isEventAlreadyProcessed($event->id);
        if ($existing && $existing['processed']) {
            http_response_code(200);
            return;
        }

        $this->logEvent($event->id, $event->type);

        try {

            switch ($event->type) {

                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->handleCheckoutSessionCompleted($session);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    $this->syncSubscriptionFromStripe($subscription);
                    break;

                case 'invoice.paid':
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    if (!empty($invoice->subscription)) {
                        $subscription = \Stripe\Subscription::retrieve([
                            'id' => $invoice->subscription,
                            'expand' => ['items.data.price']
                        ]);
                        $this->syncSubscriptionFromStripe($subscription);
                    }
                      break;
            }

            $this->markEventProcessed($event->id);

            http_response_code(200);
            echo json_encode(['received' => true]);

        } catch (\Exception $e) {
            http_response_code(500);
        }
    }

    /* ==========================================================
       STRIPE SOURCE OF TRUTH SYNC
    ========================================================== */

    private function syncSubscriptionFromStripe($subscription) {
        // Handle deleted subscriptions immediately
        if ($subscription->status === 'canceled' || $subscription->status === 'incomplete_expired') {

            $userId = $subscription->metadata->user_id ?? null;

            if (!$userId) {

                $stmt = $this->db->prepare("
                    SELECT user_id
                    FROM user_subscriptions
                    WHERE stripe_subscription_id = ?
                    LIMIT 1
                ");

                $stmt->execute([$subscription->id]);

                $userId = $stmt->fetchColumn();
            }

            if ($userId) {

                $this->updateSubscriptionStatus($subscription->id, 'canceled');

                $this->updateUserRole($userId, 1); // free role

                error_log("Subscription cancelled and user downgraded: ".$userId);
            }

            return;
        }
        try {

            if (!$subscription) {
                return;
            }

            $userId = $subscription->metadata->user_id ?? null;
            $plan   = $subscription->metadata->plan ?? null;

            /* fallback lookup if metadata missing */

            if (!$userId) {

                $stmt = $this->db->prepare("
                    SELECT user_id, plan
                    FROM user_subscriptions
                    WHERE stripe_subscription_id = ?
                    LIMIT 1
                ");

                $stmt->execute([$subscription->id]);

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    $userId = $row['user_id'];
                    $plan   = $row['plan'];
                }
            }

            if (!$userId) {
                error_log("Unable to resolve user for subscription: " . $subscription->id);
                return;
            }

            if (empty($subscription->items->data)) {
                error_log("Stripe subscription has no items");
                return;
            }

            $item = $subscription->items->data[0];

            $priceId = $item->price->id ?? null;

            $periodStart = $subscription->current_period_start ?? null;
            $periodEnd   = $subscription->current_period_end ?? null;

            if (!$periodStart || !$periodEnd) {

                $item = $subscription->items->data[0] ?? null;

                if ($item) {
                    $periodStart = $item->current_period_start ?? $periodStart;
                    $periodEnd   = $item->current_period_end ?? $periodEnd;
                }
            }

            if (!$periodStart || !$periodEnd) {
                error_log("Stripe subscription missing billing period");
                return;
            }

            $periodStart = date('Y-m-d H:i:s', $periodStart);
            $periodEnd   = date('Y-m-d H:i:s', $periodEnd);

            if (!$periodStart || !$periodEnd) {
                error_log("Stripe subscription missing billing period");
                return;
            }

            $status = $subscription->status;

            // Store subscription safely
            $this->storeSubscription(
                $userId,
                $subscription->id,
                $subscription->customer,
                $plan,
                $periodStart,
                $periodEnd,
                $status
            );

            // Update user role
            $this->updateUserRoleBasedOnStatus($userId, $status, $priceId);

        } catch (Exception $e) {

            error_log("Stripe subscription sync error: " . $e->getMessage());
        }
    }

    private function updateSubscriptionStatus($subscriptionId, $status) {
        $stmt = $this->db->prepare("
            UPDATE user_subscriptions
            SET status = ?, updated_at = NOW()
            WHERE stripe_subscription_id = ?
        ");

        $stmt->execute([$status, $subscriptionId]);
    }

    /* ==========================================================
       ROLE LOGIC
    ========================================================== */

    private function updateUserRoleBasedOnStatus($userId, $status, $priceId) {

        if (in_array($status, ['active', 'trialing'])) {

            $roleId = $this->getRoleIdFromPriceId($priceId);
            if ($roleId) {
                $this->updateUserRole($userId, $roleId);
            }

        } elseif (in_array($status, ['canceled', 'unpaid', 'incomplete_expired'])) {

            $this->updateUserRole($userId, 1);
        }

        // past_due → do nothing
    }

    public function updateUserRole($userId, $roleId) {

        $stmt = $this->db->prepare("
            INSERT INTO user_role (user_id, role_id, assigned_at)
            VALUES (?, ?, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE
                role_id = VALUES(role_id),
                assigned_at = CURRENT_TIMESTAMP
        ");

        return $stmt->execute([$userId, $roleId]);
    }

    /* ==========================================================
       STORE SUBSCRIPTION (IDEMPOTENT)
    ========================================================== */

    public function storeSubscription(
        $userId,
        $subscriptionId,
        $customerId,
        $plan,
        $periodStart,
        $periodEnd,
        $status
    ) {

        $stmt = $this->db->prepare("
            INSERT INTO user_subscriptions
            (user_id, stripe_subscription_id, stripe_customer_id, plan, status, current_period_start, current_period_end)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                stripe_customer_id = VALUES(stripe_customer_id),
                plan = VALUES(plan),
                status = VALUES(status),
                current_period_start = VALUES(current_period_start),
                current_period_end = VALUES(current_period_end),
                updated_at = NOW()
        ");

        return $stmt->execute([
            $userId,
            $subscriptionId,
            $customerId,
            $plan,
            $status,
            $periodStart,
            $periodEnd
        ]);
    }

    /* ==========================================================
       CHECKOUT HANDLER
    ========================================================== */

    private function handleCheckoutSessionCompleted($session) {

        if (!empty($session->subscription)) {
            $this->syncSubscriptionFromStripe($session->subscription);
        }
    }

    /* ==========================================================
       SAFE CANCELLATION FLOW
    ========================================================== */

    public function cancelSubscription($userId, $immediately = false) {
        $subscription = $this->getActiveSubscription($userId);

        if (!$subscription) {
            return [
                'success' => false,
                'message' => 'No active subscription found.'
            ];
        }

        $stripeSubscriptionId = $subscription['stripe_subscription_id'];

        try {

            if ($immediately) {

                $stripeSub = \Stripe\Subscription::retrieve($stripeSubscriptionId);
                $stripeSub->cancel();

            } else {

                \Stripe\Subscription::update($stripeSubscriptionId, [
                    'cancel_at_period_end' => true
                ]);
            }

            return [
                'success' => true,
                'message' => $immediately
                    ? 'Subscription cancelled immediately.'
                    : 'Subscription will cancel at the end of the billing period.'
            ];

        } catch (\Stripe\Exception\InvalidRequestException $e) {

            if (str_contains($e->getMessage(), 'No such subscription')) {

                $this->updateSubscriptionStatus($stripeSubscriptionId, 'canceled');
                $this->updateUserRole($userId, 1);

                return [
                    'success' => true,
                    'message' => 'Subscription was already cancelled.'
                ];
            }

            error_log("Cancellation error: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error cancelling subscription.'
            ];
        }
    }

    public function reactivateSubscription($userId) {

        $subscription = $this->getActiveSubscription($userId);

        if (!$subscription) {
            return ['success' => false];
        }

        \Stripe\Subscription::update($subscription['stripe_subscription_id'], [
            'cancel_at_period_end' => false
        ]);

        $this->syncSubscriptionFromStripe($subscription['stripe_subscription_id']);

        return ['success' => true];
    }

    /* ==========================================================
       HELPERS
    ========================================================== */

    public function getActiveSubscription($userId) {

        $stmt = $this->db->prepare("
            SELECT * FROM user_subscriptions
            WHERE user_id = ?
            AND status IN ('active','trialing','past_due')
            ORDER BY current_period_end DESC
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRoleIdFromPriceId($priceId) {
        return $this->priceToRole[$priceId] ?? null;
    }

    public function getPriceIdFromPlan($planName) {
        return $this->planToPrice[$planName] ?? null;
    }

    public function hasActiveSubscription($userId)
    {
        $stmt = $this->db->prepare("
            SELECT status FROM user_subscriptions
            WHERE user_id = ?
            ORDER BY current_period_end DESC
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subscription) {
            return false;
        }

        return in_array($subscription['status'], ['active', 'trialing', 'past_due']);
    }

    public function getCurrentSubscriptionPlan($userId)
    {
        $stmt = $this->db->prepare("
            SELECT plan FROM user_subscriptions
            WHERE user_id = ?
            ORDER BY current_period_end DESC
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        return $subscription['plan'] ?? 'free';
    }

    public function getDaysRemaining($userId)
    {
        $stmt = $this->db->prepare("
            SELECT current_period_end, status FROM user_subscriptions
            WHERE user_id = ?
            ORDER BY current_period_end DESC
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subscription || empty($subscription['current_period_end'])) {
            return null;
        }

        if (!in_array($subscription['status'], ['active', 'trialing', 'past_due'])) {
            return 0;
        }

        $endDate = new DateTime($subscription['current_period_end']);
        $now = new DateTime();

        if ($endDate < $now) {
            return 0;
        }

        return $now->diff($endDate)->days;
    }

    public function formatEndDate($userId)
    {
        $stmt = $this->db->prepare("
            SELECT current_period_end, status FROM user_subscriptions
            WHERE user_id = ?
            ORDER BY current_period_end DESC
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subscription || empty($subscription['current_period_end'])) {
            return 'No active subscription';
        }

        $endDate = new DateTime($subscription['current_period_end']);
        $now = new DateTime();

        if ($endDate < $now) {
            return 'Expired on ' . $endDate->format('M j, Y');
        }

        $days = $now->diff($endDate)->days;

        if ($days <= 7) {
            return 'Renews in ' . $days . ' day' . ($days !== 1 ? 's' : '');
        }

        return 'Renews on ' . $endDate->format('M j, Y');
    }

    public function createCheckoutSession($planName, $userId, $successUrl, $cancelUrl, $checkoutToken) {
        // Expire old sessions
        $this->db->prepare("
            UPDATE stripe_checkout_sessions
            SET status = 'expired'
            WHERE status = 'pending'
            AND created_at < NOW() - INTERVAL 10 MINUTE
        ")->execute();

        // Check for existing pending session
        $stmt = $this->db->prepare("
            SELECT id
            FROM stripe_checkout_sessions
            WHERE user_id = ?
            AND status = 'pending'
            LIMIT 1
        ");

        $stmt->execute([$userId]);

        if ($stmt->fetch()) {
            throw new Exception("A checkout session is already in progress.");
        }

        // Check active subscription
        if ($this->hasActiveSubscription($userId)) {
            throw new Exception("User already has active subscription.");
        }

        $priceId = $this->getPriceIdFromPlan($planName);

        if (!$priceId) {
            throw new Exception("Invalid plan selected");
        }

        // Create Stripe session
        $session = \Stripe\Checkout\Session::create([
            'mode' => 'subscription',

            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1
            ]],

            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,

            'customer' => $this->getOrCreateStripeCustomer($userId),

            'client_reference_id' => $checkoutToken,

            'metadata' => [
                'checkout_token' => $checkoutToken,
                'user_id' => $userId,
                'plan' => $planName
            ],

            'subscription_data' => [
                'metadata' => [
                    'checkout_token' => $checkoutToken,
                    'user_id' => $userId,
                    'plan' => $planName
                ]
            ]
        ]);

        // INSERT AFTER STRIPE SESSION CREATED
        $stmt = $this->db->prepare("
            INSERT INTO stripe_checkout_sessions
            (checkout_token, user_id, plan, stripe_session_id, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $checkoutToken,
            $userId,
            $planName,
            $session->id
        ]);

        return $session->url;
    }

    private function getUserEmail($userId) {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user['email'] ?? null;
    }

    private function getOrCreateStripeCustomer($userId) {
        $stmt = $this->db->prepare("
            SELECT stripe_customer_id
            FROM user_subscriptions
            WHERE user_id = ?
            AND stripe_customer_id IS NOT NULL
            LIMIT 1
        ");

        $stmt->execute([$userId]);
        $customer = $stmt->fetchColumn();

        if ($customer) {
            return $customer;
        }

        $email = $this->getUserEmail($userId);

        $stripeCustomer = \Stripe\Customer::create([
            'email' => $email,
            'metadata' => [
                'user_id' => $userId
            ]
        ]);

        return $stripeCustomer->id;
    }
}