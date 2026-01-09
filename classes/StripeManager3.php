<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

class StripeManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Initialize Stripe with secret key from config
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
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
        return STRIPE_PRICE_TO_ROLE['price_to_role'][$priceId] ?? null;
    }
    
    /**
     * Get Stripe price ID from plan name
     */
    public function getPriceIdFromPlan($planName) {
        return STRIPE_PRICE_ID['price_ids'][$planName] ?? null;
    }
    
    /**
     * Create a Stripe Checkout Session
     */
    public function createCheckoutSession($planName, $userId, $successUrl, $cancelUrl) {
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
                'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}&user_id=' . $userId . '&plan=' . $planName,
                'cancel_url' => $cancelUrl,
                'customer_email' => $this->getUserEmail($userId),
                'client_reference_id' => $userId,
                'metadata' => [
                    'user_id' => $userId,
                    'plan' => $planName,
                    'role_id' => $this->getRoleIdFromPriceId($priceId)
                ],
                'subscription_data' => [
                    'metadata' => [
                        'user_id' => $userId,
                        'plan' => $planName
                    ]
                ]
            ]);
            
            return $checkout_session->url;
            
        } catch (\Stripe\Exception\ApiErrorException $e) {
            error_log("Stripe API error: " . $e->getMessage());
            throw new Exception("Error creating checkout session: " . $e->getMessage());
        }
    }
    
    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook($payload, $sigHeader) {
        $endpoint_secret = STRIPE_SECRET['webhook_secret'];
        
        if (empty($endpoint_secret)) {
            error_log("Webhook secret not configured");
            http_response_code(500);
            echo 'Webhook secret not configured';
            exit();
        }
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpoint_secret
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
        
        $userId = $session->client_reference_id ?? $session->metadata->user_id ?? null;
        $priceId = null;
        
        // Try to get price ID from line items
        if (isset($session->line_items) && isset($session->line_items->data[0])) {
            $priceId = $session->line_items->data[0]->price->id ?? null;
        }
        
        // If not found in line items, try to get from subscription
        if (!$priceId && isset($session->subscription)) {
            try {
                $subscription = \Stripe\Subscription::retrieve($session->subscription);
                $priceId = $subscription->items->data[0]->price->id ?? null;
            } catch (\Exception $e) {
                error_log("Error retrieving subscription: " . $e->getMessage());
            }
        }
        
        if ($userId && $priceId) {
            $roleId = $this->getRoleIdFromPriceId($priceId);
            if ($roleId) {
                $success = $this->updateUserRole($userId, $roleId);
                error_log("Updated user $userId to role $roleId: " . ($success ? 'success' : 'failed'));
            } else {
                error_log("No role mapping found for price ID: " . $priceId);
            }
        } else {
            error_log("Missing user ID or price ID. User ID: $userId, Price ID: $priceId");
        }
    }
    
    /**
     * Handle subscription updates
     */
    private function handleSubscriptionUpdate($subscription) {
        error_log("Subscription updated: " . $subscription->id . " Status: " . $subscription->status);
        
        // Only process active subscriptions
        if ($subscription->status === 'active') {
            $userId = $subscription->metadata->user_id ?? null;
            $priceId = $subscription->items->data[0]->price->id ?? null;
            
            if ($userId && $priceId) {
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
    
    /**
     * Handle failed payment
     */
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
     * Get Stripe subscription details
     */
    public function getSubscriptionDetails($sessionId) {
        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId, [
                'expand' => ['subscription', 'subscription.items.data.price']
            ]);
            
            return [
                'status' => $session->payment_status,
                'subscription_id' => $session->subscription->id ?? null,
                'plan' => $session->metadata->plan ?? null,
                'amount' => $session->amount_total ? $session->amount_total / 100 : null,
                'currency' => $session->currency ?? STRIPE_SECRET['currency']
            ];
        } catch (\Exception $e) {
            error_log("Error getting subscription details: " . $e->getMessage());
            return null;
        }
    }

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
}