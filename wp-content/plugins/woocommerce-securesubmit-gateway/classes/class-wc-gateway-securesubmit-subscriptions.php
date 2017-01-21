<?php

class WC_Gateway_SecureSubmit_Subscriptions extends WC_Gateway_SecureSubmit
{
    public function __construct()
    {
        parent::__construct();

        $this->supports = array(
                            'products',
                            'refunds',
                            'subscriptions',
                            'subscription_cancellation',
                            'subscription_reactivation',
                            'subscription_suspension',
                            'subscription_amount_changes',
                            'subscription_payment_method_change',
                            'subscription_payment_method_change_admin',
                            'subscription_payment_method_change_customer',
                            'subscription_date_changes',
                            'multiple_subscriptions',
                          );

        add_action('plugins_loaded', array($this, 'init'));
    }

    public function init()
    {
        if (class_exists('WC_Subscriptions_Order')) {
            add_action('woocommerce_scheduled_subscription_payment_' . $this->id, array($this, 'scheduledSubscriptionPayment'), 10, 2);
            add_action('woocommerce_subscription_failing_payment_method_updated_' . $this->id, array($this, 'updateFailingPaymentMethod'), 10, 2);
            add_action('wcs_resubscribe_order_created', array($this, 'deleteResubscribeMeta'), 10);
            add_filter('woocommerce_subscription_payment_meta', array($this, 'addSubscriptionPaymentMeta'), 10, 2);
            add_filter('woocommerce_subscription_validate_payment_meta', array($this, 'validateSubscriptionPaymentMeta'), 10, 2);
        }
    }

    public function process_payment($orderId)
    {
        if (class_exists('WC_Subscriptions_Order') && $this->orderHasSubscription($orderId)) {
            return $this->processSubscription($orderId);
        } else {
            return parent::process_payment($orderId);
        }
    }

    protected function orderHasSubscription($order)
    {
        return function_exists('wcs_order_contains_subscription') && (wcs_order_contains_subscription($order) || wcs_order_contains_renewal($order));
    }

    protected function orderGetTotal($order)
    {
        if (method_exists($order, 'get_total')) {
            return $order->get_total();
        }
        return WC_Subscriptions_Order::get_total_initial_payment($order);
    }

    public function processSubscription($orderId)
    {
        global $woocommerce;

        $order = new WC_Order($orderId);
        $securesubmitToken = isset($_POST['securesubmit_token']) ? woocommerce_clean($_POST['securesubmit_token']) : '';
        $useStoredCard = false;

        // used for card saving:
        $last_four = isset($_POST['last_four']) ? woocommerce_clean($_POST['last_four']) : '';
        $exp_month = isset($_POST['exp_month']) ? woocommerce_clean($_POST['exp_month']) : '';
        $exp_year = isset($_POST['exp_year']) ? woocommerce_clean($_POST['exp_year']) : '';
        $card_type = isset($_POST['card_type']) ? woocommerce_clean($_POST['card_type']) : '';
        $saveCard = isset($_POST['save_card']) ? woocommerce_clean($_POST['save_card']) : '';

        try {
            if (empty($securesubmitToken)) {
                if (isset($_POST['secure_submit_card']) && $_POST['secure_submit_card'] === 'new') {
                    throw new Exception(__('Please make sure your card details have been entered correctly and that your browser supports JavaScript.', 'wc_securesubmit'));
                }
            }

            $hpstoken = new HpsTokenData();

            if (is_user_logged_in() && isset($_POST['secure_submit_card']) && $_POST['secure_submit_card'] !== 'new') {
                $cards = get_user_meta(get_current_user_id(), '_secure_submit_card', false);

                if (isset($cards[$_POST['secure_submit_card']]['token_value'])) {
                    $hpstoken->tokenValue = (string)$cards[(int)$_POST['secure_submit_card']]['token_value'];
                    $useStoredCard = true;
                } else {
                    throw new Exception(__('Invalid saved card.', 'wc_securesubmit'));
                }
            } else {
                $hpstoken->tokenValue = $securesubmitToken;
            }

            try {
                $saveCardToCustomer = !$useStoredCard;
                $initialPayment = $this->orderGetTotal($order);
                $response = null;
                if ($initialPayment >= 0) {
                    $response = $this->processSubscriptionPayment($order, $initialPayment, $hpstoken, $saveCardToCustomer);
                }

                if (isset($response) && is_wp_error($response)) {
                    throw new Exception($response->get_error_message());
                }

                if ($saveCardToCustomer) {
                    if (is_user_logged_in()) {
                        $tokenval = (string)$response->tokenData->tokenValue;
                        $saveToOrder = false;

                        if ($response->tokenData->responseCode == '0' && !$useStoredCard) {
                            add_user_meta(get_current_user_id(), '_secure_submit_card', array(
                                'last_four' => $last_four,
                                'exp_month' => $exp_month,
                                'exp_year' => $exp_year,
                                'token_value' => $tokenval,
                                'card_type' => $card_type,
                            ));
                            $saveToOrder = true;
                        }
                    }
                }

                if ($useStoredCard) {
                    $tokenval = $hpstoken->tokenValue;
                    $saveToOrder = true;
                }

                if ($saveToOrder) {
                    $this->saveTokenMeta($order, $tokenval);
                }

                $woocommerce->cart->empty_cart();

                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            } catch (HpsException $e) {
                throw new Exception(__((string)$e->getMessage(), 'wc_securesubmit'));
            }
        } catch (Exception $e) {
            $error = __('Error:', 'wc_securesubmit') . ' "' . (string)$e->getMessage() . '"';
            if (function_exists('wc_add_notice')) {
                wc_add_notice($error, 'error');
            } else {
                $woocommerce->add_error($error);
            }
            return;
        }
    }

    protected function saveTokenMeta($order, $token)
    {
        add_post_meta($order->id, '_securesubmit_card_token', $token, true);
        // save to subscriptions in order
        foreach(wcs_get_subscriptions_for_order($order->id) as $subscription) {
            update_post_meta($subscription->id, '_securesubmit_card_token', $token);
        }
    }

    public function scheduledSubscriptionPayment($amount, $order, $productId = null)
    {
        // TODO: why is this necessary to prevent double authorization?
        if ($order->post_status !== 'wc-pending') {
            return;
        }

        $result = $this->processSubscriptionPayment($order, $amount);

        if (is_wp_error($result)) {
            $order->update_status('failed', sprintf(__('SecureSubmit transaction failed: %s', 'wc_securesubmit'), $result->get_error_message()));
        }
    }

    public function processSubscriptionPayment($order, $amount, $tokenData = null, $requestMulti = false)
    {
        global $woocommerce;

        if (!is_object($order)) {
            $order = new WC_Order($order);
        }
        $tokenValue = get_post_meta($order->id, '_securesubmit_card_token', true);
        $token = new HpsTokenData();
        $token->tokenValue = $tokenValue;

        if (!isset($tokenValue) && $tokenData == null) {
            return new WP_Error('securesubmit_error', __('SecureSubmit payment token not found', 'wc_securesubmit'));
        }

        if ($tokenData != null) {
            $token = $tokenData;
        }

        try {
            $chargeService = $this->getCreditService();
            $hpsaddress = $this->getOrderAddress($order);
            $cardHolder = $this->getOrderCardHolder($order, $hpsaddress);

            $details = new HpsTransactionDetails();
            $details->invoiceNumber = $order->id;

            $response = null;
            if ($amount == 0) {
                $response = $chargeService->verify(
                    $token,
                    $cardHolder,
                    $requestMulti
                );
            } else {
                $response = $chargeService->charge(
                    $amount,
                    strtolower(get_woocommerce_currency()),
                    $token,
                    $cardHolder,
                    $requestMulti,
                    $details
                );
            }

            $order->payment_complete($response->transactionId);
            $order->add_order_note(sprintf(
                __('SecureSubmit %s completed (Transaction ID: %s)', 'wc_securesubmit'),
                ($amount == 0 ? 'verify' : 'payment'),
                $response->transactionId
            ));
            add_post_meta($order->id, '_transaction_id', $response->transactionId, true);

            return $response;
        } catch (Exception $e) {
            return new WP_Error('securesubmit_error', sprintf(__('SecureSubmit payment error: %s', 'wc_securesubmit'), (string)$e->getMessage()));
        }
    }

    public function updateFailingPaymentMethod($old, $new, $key = null)
    {
        update_post_meta($old->id, '_securesubmit_card_token', get_post_meta($new->id, '_securesubmit_card_token', true));
    }

    public function addSubscriptionPaymentMeta($meta, $subscription)
    {
        $meta[$this->id] = array(
            'post_meta' => array(
                '_securesubmit_card_token' => array(
                    'value' => get_post_meta($subscription->id, '_securesubmit_card_token', true),
                    'label' => 'SecureSubmit payment token',
                ),
            ),
        );
        return $meta;
    }

    public function validateSubscriptionPaymentMeta($methodId, $meta)
    {
        if ($this->id === $methodId) {
            $token = $meta['post_meta']['_securesubmit_card_token']['value'];
            if (!isset($token) || empty($token)) {
                throw new Exception(__('A SecureSubmit payment token is required.', 'wc_securesubmit'));
            }
        }
    }

    public function deleteResubscribeMeta($order)
    {
        delete_user_meta($order->id, '_securesubmit_card_token');
    }
}
new WC_Gateway_SecureSubmit_Subscriptions();
