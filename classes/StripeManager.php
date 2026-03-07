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
                    $this->syncSubscriptionFromStripe($subscription->id);
                    break;

                case 'invoice.paid':
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    if (!empty($invoice->subscription)) {
                        $this->syncSubscriptionFromStripe($invoice->subscription);
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

    private function syncSubscriptionFromStripe($subscriptionId) {

        $subscription = \Stripe\Subscription::retrieve([
            'id' => $subscriptionId,
            'expand' => ['items.data.price']
        ]);

        $userId = $subscription->metadata->user_id ?? null;
        $plan   = $subscription->metadata->plan ?? null;

        if (!$userId || empty($subscription->items->data)) {
            return;
        }

        $item = $subscription->items->data[0];

        if (!isset($item->current_period_start) || !isset($item->current_period_end)) {
            return; // Never fake billing dates
        }

        $periodStart = date('Y-m-d H:i:s', $item->current_period_start);
        $periodEnd   = date('Y-m-d H:i:s', $item->current_period_end);

        $priceId = $item->price->id ?? null;
        $status  = $subscription->status;

        $this->storeSubscription(
            $userId,
            $subscription->id,
            $subscription->customer,
            $plan,
            $periodStart,
            $periodEnd,
            $status
        );

        $this->updateUserRoleBasedOnStatus($userId, $status, $priceId);
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
            return ['success' => false, 'message' => 'No active subscription found.'];
        }

        $stripeSubscriptionId = $subscription['stripe_subscription_id'];

        if ($immediately) {

            \Stripe\Subscription::cancel($stripeSubscriptionId);

        } else {

            \Stripe\Subscription::update($stripeSubscriptionId, [
                'cancel_at_period_end' => true
            ]);
        }

        // Let webhook sync state
        $this->syncSubscriptionFromStripe($stripeSubscriptionId);

        return ['success' => true];
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
}