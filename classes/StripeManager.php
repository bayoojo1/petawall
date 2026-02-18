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
        
        // Load configuration
        $this->loadConfig();
        
        // Initialize Stripe
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    }
    
    private function loadConfig() {
        // These should be defined in config.php
        if (!defined('STRIPE_SECRET_KEY')) {
            throw new Exception('Stripe configuration not found. Please check config.php');
        }
        
        // Initialize arrays from config.php or use defaults
        global $stripePriceToRole, $stripePlanToPrice;
        
        $this->priceToRole = $stripePriceToRole ?? [
            'price_1Slv7hReUJdbdkCUWse4Cddp' => 2, // basic
            'price_1Slv9KReUJdbdkCUba1o3mIj' => 3  // premium
        ];
        
        $this->planToPrice = $stripePlanToPrice ?? [
            'basic' => 'price_1Slv7hReUJdbdkCUWse4Cddp',
            'premium' => 'price_1Slv9KReUJdbdkCUba1o3mIj'
        ];
    }
    
    /**
     * Update user role based on Stripe subscription
     */
    public function updateUserRole($userId, $roleId) {
        try {
            // First, check if user exists in user_role table
            $stmt = $this->db->prepare("SELECT user_id FROM user_role WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            if ($stmt->rowCount() > 0) {
                // Update existing record
                $stmt = $this->db->prepare("UPDATE user_role SET role_id = ?, assigned_at = CURRENT_TIMESTAMP WHERE user_id = ?");
                $stmt->execute([$roleId, $userId]);
            } else {
                // Insert new record
                $stmt = $this->db->prepare("INSERT INTO user_role (user_id, role_id, assigned_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
                $stmt->execute([$userId, $roleId]);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Database error in updateUserRole: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get role ID from Stripe price ID
     */
    public function getRoleIdFromPriceId($priceId) {
        return $this->priceToRole[$priceId] ?? null;
    }
    
    /**
     * Get Stripe price ID from plan name
     */
    public function getPriceIdFromPlan($planName) {
        return $this->planToPrice[$planName] ?? null;
    }

    public function createCheckoutSession($planName, $userId, $successUrl, $cancelUrl, $checkoutToken) {
        try {
            $priceId = $this->getPriceIdFromPlan($planName);
            
            if (!$priceId) {
                throw new Exception("Invalid plan name: " . $planName);
            }
            
            $checkout_session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $this->getUserEmail($userId),
                'client_reference_id' => $checkoutToken, // Use token as reference
                'metadata' => [
                    'checkout_token' => $checkoutToken, // Store token in metadata
                    'user_id' => $userId,
                    'plan' => $planName,
                    'role_id' => $this->getRoleIdFromPriceId($priceId)
                ],
                'subscription_data' => [
                    'metadata' => [
                        'checkout_token' => $checkoutToken,
                        'user_id' => $userId,
                        'plan' => $planName
                    ]
                ]
            ]);
            
            // Store Stripe session ID in database
            $this->updateCheckoutSessionWithStripeId($checkoutToken, $checkout_session->id);
            
            return $checkout_session->url;
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe API error: " . $e->getMessage());
            throw new Exception("Error creating checkout session: " . $e->getMessage());
        }
    }

    private function updateCheckoutSessionWithStripeId($checkoutToken, $stripeSessionId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE stripe_checkout_sessions 
                SET stripe_session_id = ? 
                WHERE checkout_token = ?
            ");
            $stmt->execute([$stripeSessionId, $checkoutToken]);
        } catch (PDOException $e) {
            error_log("Failed to update checkout session: " . $e->getMessage());
        }
    }
    
    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook($payload, $sigHeader) {
        if (!defined('STRIPE_WEBHOOK_SECRET')) {
            error_log("Webhook secret not configured");
            http_response_code(500);
            echo 'Webhook secret not configured';
            exit();
        }
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, STRIPE_WEBHOOK_SECRET
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            error_log("Webhook invalid payload: " . $e->getMessage());
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            error_log("Webhook invalid signature: " . $e->getMessage());
            http_response_code(400);
            exit();
        }
        
        // Log the event type
        error_log("Stripe webhook received: " . $event->type);
        
        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                
                // Get checkout token from metadata
                $checkoutToken = $session->metadata->checkout_token ?? null;
                
                if ($checkoutToken) {
                    // Update the checkout session in database
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        UPDATE stripe_checkout_sessions 
                        SET status = 'completed', completed_at = NOW() 
                        WHERE checkout_token = ? AND status = 'pending'
                    ");
                    $stmt->execute([$checkoutToken]);
                }
                
                // Continue with existing role update logic...
                $this->handleCheckoutSessionCompleted($session);
                break;
                
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdate($subscription);
                break;
                
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $this->handleSubscriptionCancelled($subscription);
                break;
                
            case 'invoice.paid':
                $invoice = $event->data->object;
                $this->handleInvoicePaid($invoice);
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $this->handlePaymentFailed($invoice);
                break;

            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                
                // Check if subscription was cancelled
                if ($subscription->status === 'canceled') {
                    $userId = $subscription->metadata->user_id ?? null;
                    if ($userId) {
                        // Update database
                        $stripeManager->updateSubscriptionStatus($userId, 'canceled');
                        // Downgrade user to free
                        $stripeManager->updateUserRole($userId, 1);
                        error_log("Subscription cancelled via webhook for user: $userId");
                    }
                }
                
                // Handle scheduled cancellations
                if ($subscription->cancel_at_period_end) {
                    $userId = $subscription->metadata->user_id ?? null;
                    if ($userId) {
                        $stripeManager->updateSubscriptionCancelAtPeriodEnd($userId, true);
                        error_log("Subscription scheduled for cancellation for user: $userId");
                    }
                }
                
                // Handle reactivations
                if (!$subscription->cancel_at_period_end) {
                    $userId = $subscription->metadata->user_id ?? null;
                    if ($userId) {
                        $stripeManager->updateSubscriptionCancelAtPeriodEnd($userId, false);
                        error_log("Subscription reactivated for user: $userId");
                    }
                }
                
                break;
                
            default:
                // Log unhandled events but don't fail
                error_log("Unhandled event type: " . $event->type);
        }
        
        http_response_code(200);
        echo json_encode(['received' => true]);
    }

    /**
 * Handle successful checkout
 */

    private function handleCheckoutSessionCompleted($session) {
        error_log("Checkout session completed: " . $session->id);
        
        $userId = $session->metadata->user_id ?? $session->client_reference_id ?? null;
        $checkoutToken = $session->metadata->checkout_token ?? null;
        $subscriptionId = $session->subscription ?? null;
        $customerId = $session->customer ?? null;
        $plan = $session->metadata->plan ?? null;
        
        error_log("Checkout session data - user_id: $userId, subscription_id: $subscriptionId, plan: $plan, customer_id: $customerId");
        
        if ($subscriptionId) {
            try {
                // Retrieve the subscription - don't need to expand anything for period dates
                $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                error_log("Retrieved subscription: " . $subscription->id . ", status: " . $subscription->status);
                
                $priceId = $subscription->items->data[0]->price->id ?? null;
                $plan = $subscription->metadata->plan ?? $plan;
                $customerId = $subscription->customer ?? $customerId;
                
                // Get period dates correctly
                $periodStart = isset($subscription->current_period_start) 
                    ? date('Y-m-d H:i:s', $subscription->current_period_start)
                    : date('Y-m-d H:i:s');
                    
                $periodEnd = isset($subscription->current_period_end)
                    ? date('Y-m-d H:i:s', $subscription->current_period_end)
                    : date('Y-m-d H:i:s', strtotime('+30 days'));
                
                error_log("Subscription details - price_id: $priceId, plan: $plan, customer_id: $customerId");
                error_log("Period: $periodStart to $periodEnd");
                
                if ($userId && $priceId && $plan && $customerId) {
                    // Store subscription with proper dates
                    $this->storeSubscription(
                        $userId,
                        $subscription->id,
                        $customerId,
                        $plan,
                        $periodStart,
                        $periodEnd,
                        $subscription->status
                    );
                    
                    // Update user role
                    $roleId = $this->getRoleIdFromPriceId($priceId);
                    if ($roleId) {
                        $success = $this->updateUserRole($userId, $roleId);
                        error_log("Checkout completed: User $userId to role $roleId: " . ($success ? 'success' : 'failed'));
                    } else {
                        error_log("No role mapping found for price ID: " . $priceId);
                    }
                } else {
                    error_log("Missing data for subscription storage. User: $userId, Price: $priceId, Plan: $plan, Customer: $customerId");
                }
            } catch (\Exception $e) {
                error_log("Error retrieving subscription: " . $e->getMessage());
            }
        } else {
            error_log("No subscription ID found in session");
        }
    }

    private function handleSubscriptionUpdate($subscription) {
        error_log("Subscription updated: " . $subscription->id . " Status: " . $subscription->status);
        
        $userId = $subscription->metadata->user_id ?? null;
        $priceId = $subscription->items->data[0]->price->id ?? null;
        $plan = $subscription->metadata->plan ?? null;
        
        // Get period dates correctly - these are top-level properties
        $periodStart = null;
        $periodEnd = null;
        
        if (isset($subscription->current_period_start)) {
            $periodStart = date('Y-m-d H:i:s', $subscription->current_period_start);
            error_log("Current period start: " . $subscription->current_period_start . " -> " . $periodStart);
        } else {
            error_log("current_period_start not set on subscription object");
            // Set a default (30 days from now)
            $periodStart = date('Y-m-d H:i:s');
        }
        
        if (isset($subscription->current_period_end)) {
            $periodEnd = date('Y-m-d H:i:s', $subscription->current_period_end);
            error_log("Current period end: " . $subscription->current_period_end . " -> " . $periodEnd);
        } else {
            error_log("current_period_end not set on subscription object");
            // Set a default (30 days from now)
            $periodEnd = date('Y-m-d H:i:s', strtotime('+30 days'));
        }
        
        if ($userId && $priceId && $plan) {
            // Store subscription details with proper dates
            $this->storeSubscription(
                $userId,
                $subscription->id,
                $subscription->customer,
                $plan,
                $periodStart,
                $periodEnd,
                $subscription->status
            );
            
            // Update user role if subscription is active
            if ($subscription->status === 'active') {
                $roleId = $this->getRoleIdFromPriceId($priceId);
                if ($roleId) {
                    $success = $this->updateUserRole($userId, $roleId);
                    error_log("Subscription update: User $userId to role $roleId: " . ($success ? 'success' : 'failed'));
                }
            }
        }
    }
    
    /**
     * Handle subscription cancellation
     */
    private function handleSubscriptionCancelled($subscription) {
        error_log("Subscription cancelled: " . $subscription->id);
        
        $userId = $subscription->metadata->user_id ?? null;
        
        if ($userId) {
            // Downgrade to free plan (role_id 1)
            $success = $this->updateUserRole($userId, 1); // Assuming 1 is free plan
            error_log("Subscription cancelled: Downgraded user $userId to free plan: " . ($success ? 'success' : 'failed'));
        }
    }
    
    /**
     * Handle paid invoice
     */
    private function handleInvoicePaid($invoice) {
        error_log("Invoice paid: " . $invoice->id);
        
        // This ensures recurring payments also update roles
        if (isset($invoice->subscription)) {
            try {
                $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
                $this->handleSubscriptionUpdate($subscription);
            } catch (\Exception $e) {
                error_log("Error handling invoice paid: " . $e->getMessage());
            }
        }
    }

    private function handlePaymentFailed($invoice) {
        error_log("Payment failed for invoice: " . $invoice->id);
        
        // Optionally handle failed payments (e.g., send notification)
        if (isset($invoice->subscription)) {
            try {
                $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
                $userId = $subscription->metadata->user_id ?? null;
                
                if ($userId) {
                    // You might want to downgrade after multiple failed payments
                    // or send a notification to the user
                    $success = $this->updateUserRole($userId, 1); // Assuming 1 is free plan
            error_log("Payment failed: Downgraded user $userId to free plan: " . ($success ? 'success' : 'failed'));
                    error_log("Payment failed for user: " . $userId);
                }
            } catch (\Exception $e) {
                error_log("Error handling payment failed: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Get user email from database
     */
    private function getUserEmail($userId) {
        try {
            // Try different possible column names
            $stmt = $this->db->prepare("SELECT email FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['email'] ?? null;
        } catch (PDOException $e) {
            error_log("Error getting user email: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription($userId) {
        try {
            // Query the user_role table to check if user has paid role
            $stmt = $this->db->prepare("SELECT role_id FROM user_role WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $roleId = $result['role_id'];
                // Check if role_id is a paid plan (2 = basic, 3 = premium)
                return in_array($roleId, [2, 3]);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Check subscription error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current subscription plan for user
     */
    public function getCurrentSubscriptionPlan($userId) {
        try {
            $stmt = $this->db->prepare("SELECT role_id FROM user_role WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $roleId = $result['role_id'];
                // Map role_id to plan name
                $roleToPlan = [
                    1 => 'free',
                    2 => 'basic',
                    3 => 'premium'
                ];
                return $roleToPlan[$roleId] ?? 'free';
            }
            
            return 'free';
        } catch (PDOException $e) {
            error_log("Get subscription plan error: " . $e->getMessage());
            return 'free';
        }
    }

    /**
     * Store subscription information in database
     */
    public function storeSubscription($userId, $subscriptionId, $customerId, $plan, $periodStart, $periodEnd, $status = 'active') {
        try {
            error_log("Attempting to store subscription for user: $userId, subscription: $subscriptionId, plan: $plan, status: $status");
            
            // First, check if this subscription already exists
            $checkStmt = $this->db->prepare("
                SELECT id, status FROM user_subscriptions 
                WHERE stripe_subscription_id = ?
            ");
            $checkStmt->execute([$subscriptionId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Update existing subscription
                $updateStmt = $this->db->prepare("
                    UPDATE user_subscriptions 
                    SET user_id = ?, 
                        stripe_customer_id = ?,
                        plan = ?,
                        status = ?, 
                        current_period_start = ?, 
                        current_period_end = ?,
                        updated_at = NOW()
                    WHERE stripe_subscription_id = ?
                ");
                $success = $updateStmt->execute([
                    $userId, 
                    $customerId, 
                    $plan, 
                    $status, 
                    $periodStart, 
                    $periodEnd, 
                    $subscriptionId
                ]);
                
                error_log("Updated existing subscription $subscriptionId: " . ($success ? 'success' : 'failed'));
            } else {
                // Insert new subscription
                $insertStmt = $this->db->prepare("
                    INSERT INTO user_subscriptions 
                    (user_id, stripe_subscription_id, stripe_customer_id, plan, status, current_period_start, current_period_end) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $success = $insertStmt->execute([
                    $userId, 
                    $subscriptionId, 
                    $customerId, 
                    $plan, 
                    $status, 
                    $periodStart, 
                    $periodEnd
                ]);
                
                error_log("Inserted new subscription $subscriptionId: " . ($success ? 'success' : 'failed'));
            }
            
            // Debug: Count subscriptions for this user
            $countStmt = $this->db->prepare("SELECT COUNT(*) as count FROM user_subscriptions WHERE user_id = ?");
            $countStmt->execute([$userId]);
            $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            error_log("Total subscriptions for user $userId: $count");
            
            return $success;
            
        } catch (PDOException $e) {
            error_log("Store subscription error: " . $e->getMessage());
            error_log("Error details: user_id=$userId, subscription_id=$subscriptionId, plan=$plan");
            return false;
        }
    }

    public function getActiveSubscription($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_subscriptions 
                WHERE user_id = ? AND status = 'active' 
                ORDER BY current_period_end DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get subscription error: " . $e->getMessage());
            return null;
        }
    }

    public function getDaysRemaining($userId) {
        $subscription = $this->getActiveSubscription($userId);
        
        if (!$subscription || empty($subscription['current_period_end'])) {
            return null;
        }
        
        $endDate = new DateTime($subscription['current_period_end']);
        $now = new DateTime();
        
        if ($endDate < $now) {
            return 0; // Already expired
        }
        
        $interval = $now->diff($endDate);
        return $interval->days;
    }

    /**
     * Format subscription end date
     */
    public function formatEndDate($userId) {
        $subscription = $this->getActiveSubscription($userId);
        
        if (!$subscription || empty($subscription['current_period_end'])) {
            return 'No active subscription';
        }
        
        $endDate = new DateTime($subscription['current_period_end']);
        $now = new DateTime();
        
        if ($endDate < $now) {
            return 'Expired on ' . $endDate->format('M j, Y');
        }
        
        // If within 7 days, show "in X days", otherwise show date
        $interval = $now->diff($endDate);
        
        if ($interval->days <= 7) {
            return 'Renews in ' . $interval->days . ' day' . ($interval->days !== 1 ? 's' : '');
        } else {
            return 'Renews on ' . $endDate->format('M j, Y');
        }
    }

    /**
     * Cancel user's subscription
     */
    public function cancelSubscription($userId, $immediately = false) {
        try {
            // Get active subscription
            $subscription = $this->getActiveSubscription($userId);
            
            if (!$subscription || empty($subscription['stripe_subscription_id'])) {
                return [
                    'success' => false,
                    'message' => 'No active subscription found.'
                ];
            }
            
            $stripeSubscriptionId = $subscription['stripe_subscription_id'];
            
            // Cancel via Stripe API
            if ($immediately) {
                // Cancel immediately
                $stripeSubscription = \Stripe\Subscription::retrieve($stripeSubscriptionId);
                $stripeSubscription->cancel();
                
                // Update database status
                $this->updateSubscriptionStatus($userId, 'canceled');
                
                // Downgrade user to free plan immediately
                $this->updateUserRole($userId, 1); // Assuming 1 is free
                
                return [
                    'success' => true,
                    'message' => 'Subscription cancelled immediately. You have been downgraded to the Free plan.'
                ];
                
            } else {
                // Schedule cancellation at period end
                $stripeSubscription = \Stripe\Subscription::update($stripeSubscriptionId, [
                    'cancel_at_period_end' => true
                ]);
                
                // Update database
                $this->updateSubscriptionCancelAtPeriodEnd($userId, true);
                
                $periodEnd = date('F j, Y', $stripeSubscription->current_period_end);
                
                return [
                    'success' => true,
                    'message' => "Subscription scheduled for cancellation. You will have access until {$periodEnd}."
                ];
            }
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe cancellation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error cancelling subscription: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            error_log("Cancellation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while cancelling your subscription.'
            ];
        }
    }

    /**
     * Reactivate a scheduled cancellation
     */
    public function reactivateSubscription($userId) {
        try {
            // Get subscription with pending cancellation
            $subscription = $this->getSubscriptionWithPendingCancellation($userId);
            
            if (!$subscription || empty($subscription['stripe_subscription_id'])) {
                return [
                    'success' => false,
                    'message' => 'No subscription with pending cancellation found.'
                ];
            }
            
            $stripeSubscriptionId = $subscription['stripe_subscription_id'];
            
            // Reactivate in Stripe
            $stripeSubscription = \Stripe\Subscription::update($stripeSubscriptionId, [
                'cancel_at_period_end' => false
            ]);
            
            // Update database
            $this->updateSubscriptionCancelAtPeriodEnd($userId, false);
            
            return [
                'success' => true,
                'message' => 'Subscription reactivated successfully!'
            ];
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe reactivation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error reactivating subscription: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            error_log("Reactivation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while reactivating your subscription.'
            ];
        }
    }

    /**
     * Get subscription with pending cancellation
     */
    public function getSubscriptionWithPendingCancellation($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_subscriptions 
                WHERE user_id = ? 
                AND status = 'active'
                AND cancel_at_period_end = 1
                ORDER BY current_period_end DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get subscription with pending cancellation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update subscription status
     */
    private function updateSubscriptionStatus($userId, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_subscriptions 
                SET status = ?, updated_at = NOW()
                WHERE user_id = ? AND status = 'active'
            ");
            return $stmt->execute([$status, $userId]);
        } catch (PDOException $e) {
            error_log("Update subscription status error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update cancel_at_period_end flag
     */
    private function updateSubscriptionCancelAtPeriodEnd($userId, $cancelAtPeriodEnd) {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_subscriptions 
                SET cancel_at_period_end = ?, updated_at = NOW()
                WHERE user_id = ? AND status = 'active'
            ");
            return $stmt->execute([$cancelAtPeriodEnd ? 1 : 0, $userId]);
        } catch (PDOException $e) {
            error_log("Update cancel_at_period_end error: " . $e->getMessage());
            return false;
        }
    }
}