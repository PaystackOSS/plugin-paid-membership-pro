<?php
// Paystack webhook handler for Paid Memberships Pro.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// define( 'PMPRO_PAYSTACK_WEBHOOK_DEBUG', 'log' );

// Let's make sure the request came from Paystack by checking the secret key
if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || ( strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) != 'POST' ) || ! array_key_exists( 'HTTP_X_PAYSTACK_SIGNATURE', $_SERVER ) ) {
    pmpro_paystack_webhook_log( 'Paystack signature not found' );
    pmpro_paystack_webhook_exit();
}

// Log all the post data.
pmpro_paystack_webhook_log( 'Request:' . print_r( $_REQUEST, true ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Webhook request from Paystack; read-only logging; the request is authenticated by the HMAC signature check below.

// Get the relevant secret key based on gateway environment.
$gateway_environment = get_option( 'pmpro_gateway_environment' );

if ( $gateway_environment == 'sandbox' ) {
    $secret_key = get_option( 'pmpro_paystack_tsk' );
    $public_key = get_option( 'pmpro_paystack_tpk' );
} else {
    $secret_key = get_option( 'pmpro_paystack_lsk' );
    $public_key = get_option( 'pmpro_paystack_lpk' );
}

// Without a secret key for this environment, the signature can't be verified, so let's bail.
if ( empty( $secret_key ) ) {
    pmpro_paystack_webhook_log( 'Paystack secret key not set for the current gateway environment.' );
    pmpro_paystack_webhook_exit();
}

// Get the input from Paystack.
$body = @file_get_contents( 'php://input' );
$post_event = json_decode( $body );

// The Paystack signature doesn't match the secret key, let's bail.
if ( ! hash_equals( hash_hmac( 'sha512', $body, $secret_key ), sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ) ) ) ) {
    pmpro_paystack_webhook_log( 'Paystack signature does not match.' );
    pmpro_paystack_webhook_exit();
}

pmpro_paystack_webhook_log( 'Event: ' . print_r( $post_event, true ) );

switch( $post_event->event ){
case 'subscription.disable':
    pmpro_handle_subscription_cancellation_at_gateway( $post_event->data->subscription_code, 'paystack', $gateway_environment );
    pmpro_paystack_webhook_exit();
    break;
case 'charge.success': // This runs for both recurring and initial checkouts.
    $morder =  new MemberOrder($post_event->data->reference);
    $morder->getMembershipLevel();
    $morder->getUser();
    pmpro_paystack_complete_order( $post_event->data->reference, $morder );
    pmpro_paystack_confirm_subscription( $post_event, $morder ); // This will be for recurring subscriptions/levels.
    $pstk_logger = new pmpro_paystack_plugin_tracker( 'pm-pro', $public_key );
    $pstk_logger->log_transaction_success( $post_event->data->reference );
    pmpro_paystack_webhook_log( 'Charge success. Reference: ' . $post_event->data->reference );
    break;
case 'invoice.create':
    pmpro_paystack_renew_payment( $post_event );
    break;
case 'invoice.update':
    pmpro_paystack_renew_payment($post_event); 
    break;
}
http_response_code(200);
pmpro_paystack_webhook_exit();


/**
 * Complete the PMPro order.
 *
 * @param [type] $reference
 * @param [type] $morder
 * @return void
 */
function pmpro_paystack_complete_order( $reference, &$order ) {

    // No reference passed, let's bail.
    if ( empty( $reference ) ) {
        return false;
    }

    // Only run this if we got an order.
    if ( !isset( $order->code ) ) {
        return false;
    }

    // If not object let's bail.
    if ( ! is_object( $order ) ) {
        return false;
    }

    // Order is not a Paystack order, so we can just bail.
    if ( $order->gateway != 'paystack' ) {
        return false;
    }

    // Order is not in token status, so we can just bail - no need to update anything.
    if ( $order->status != 'token' ) {
        return false;
    }

    // Reference is for another order
    if ( $order->code != $reference ) {
        return false;
    }


	// update order status and transaction ids
	$order->payment_transaction_id = $reference;
	$order->saveOrder(); // Temporarily save the order before processing it.

	// Change level and complete the order.
	pmpro_pull_checkout_data_from_order( $order );
	return pmpro_complete_async_checkout( $order );
	
}

// Confirm the subscription via plan etc.
function pmpro_paystack_confirm_subscription( $post_event, $order ) {
    global $wpdb, $current_user, $pmpro_invoice, $pmpro_currency,$gateway;

    $webhook_reference_id = $post_event->data->reference;

    if (empty($pmpro_invoice)) {
        $morder =  new MemberOrder($webhook_reference_id);
        if (!empty($morder) && $morder->gateway == "paystack") {
            $pmpro_invoice = $morder;
        }
    }

    // No user found lets bail then.
    if (!empty($pmpro_invoice) && $pmpro_invoice->gateway == "paystack" && isset($pmpro_invoice->total) && $pmpro_invoice->total > 0) {
            $morder = $pmpro_invoice;
        if ($morder->code == $webhook_reference_id ) {

            // TODO: Use pmpro_getLevel instead of a DB query.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- PMPro custom table; the only variable is cast with (int).
            $pmpro_level = $wpdb->get_row("SELECT * FROM $wpdb->pmpro_membership_levels WHERE id = '" . (int)$morder->membership_id . "' LIMIT 1");
			
			// TODO: Move to pmpro_calculate_profile_start_date.
            $startdate = apply_filters("pmpro_checkout_start_date", "'" . current_time("mysql") . "'", $morder->user_id, $pmpro_level);

            // The level object from the order that can be filtered when returning from Paystack and user is getting their membership level.
            // TODO: Deprecate this.
            $morder->membership_level = apply_filters( 'pmpro_paystack_webhook_level', $morder->membership_level, $morder->user_id );

            // Get the mode of the environment so we know which keys to use.
            $mode = pmpro_getOption("gateway_environment");
            if ($mode == "sandbox") {
                $key = pmpro_getOption("paystack_tsk");
                $pk = pmpro_getOption("paystack_tpk");
            } else {
                $key = pmpro_getOption("paystack_lsk");
                $pk = pmpro_getOption("paystack_lpk");
            }
            $paystack_url = 'https://api.paystack.co/transaction/verify/' . $webhook_reference_id;
            $headers = array(
                'Authorization' => 'Bearer ' . $key
            );
            $args = array(
                'headers'   => $headers,
                'timeout'   => 60
            );
            $request = wp_remote_get( $paystack_url, $args );
            
            if (!is_wp_error($request) && 200 == wp_remote_retrieve_response_code($request) ) {
                $paystack_response = json_decode(wp_remote_retrieve_body($request));

                if ( 'success' == $paystack_response->data->status ) {
                    $customer_code = $paystack_response->data->customer->customer_code;

                    //Add logger here
                    $pstk_logger = new pmpro_paystack_plugin_tracker('pm-pro',$pk);
                    $pstk_logger->log_transaction_success($webhook_reference_id);

                    // Get level from the order.
                    $pmpro_level = $morder->getMembershipLevel();

                    // There's recurring settings, lets convert to Paystack intervals now.
                    if ( $pmpro_level->billing_amount > 0 ) {

                        // Convert the PMPro cycle to match that of paystacks.
                        $pmpro_paystack = new PMProGateway_paystack();
                        $interval = $pmpro_paystack->convert_interval_for_paystack( $pmpro_level->cycle_period );

                        // Biannual and quarterly conversion for special cases.
                        if ( $pmpro_level->cycle_number == 3 && $pmpro_level->cycle_period == 'Month' ) {
                            $interval = 'quarterly';
                        }

                        if ( $pmpro_level->cycle_number == 6 && $pmpro_level->cycle_period == 'Month' ) {
                            $interval = 'biannually';
                        }

                        $amount = $pmpro_level->billing_amount;
                        $koboamount = $amount*100;
                        //Create Plan
                        $paystack_url = 'https://api.paystack.co/plan';
                        $subscription_url = 'https://api.paystack.co/subscription';
                        $check_url = 'https://api.paystack.co/plan?amount='.$koboamount.'&interval='.$interval;
                        $headers = array(
                            'Content-Type'  => 'application/json',
                            'Authorization' => 'Bearer ' . $key
                        );

                        $checkargs = array(
                            'headers' => $headers,
                            'timeout' => 60
                        );
                        // Check if plan exist
                        $checkrequest = wp_remote_get($check_url, $checkargs);
                        if (!is_wp_error($checkrequest)) {
                            $response = json_decode(wp_remote_retrieve_body($checkrequest));
                            if ($response->meta->total >= 1) {
                                $plan = $response->data[0];
                                $plancode = $plan->plan_code;

                            } else {
                                //Create Plan
                                $body = array(
                                    'name'      => apply_filters( 'pmpro_paystack_plan_description', substr( trim( $pmpro_level->name ) . " at " . trim( get_bloginfo( "name" ) ), 0, 127 ), $pmpro_level ),
                                    'amount'    => $koboamount,
                                    'interval'  => $interval
                                );
                                $args = array(
                                    'body'      => json_encode($body),
                                    'headers'   => $headers,
                                    'timeout'   => 60
                                );

                                $request = wp_remote_post($paystack_url, $args);
                                if (!is_wp_error($request)) {
                                    $paystack_response = json_decode(wp_remote_retrieve_body($request));
                                    $plancode = $paystack_response->data->plan_code;
                                }
                            }

                        }
                        
                        $subscription_delay = get_option( 'pmpro_subscription_delay_' . $pmpro_level->id, 0 );

                        $body = array(
                            'customer'  => $customer_code,
                            'plan'      => $plancode
                        );
                        
                        if ( $subscription_delay ) {
                                if ( ! is_numeric( $subscription_delay ) ) {
                                $start_date = kkd_pmprosd_convert_date( $subscription_delay );
                            } else {
                                $start_date = date( 'Y-m-d', strtotime( '+ ' . intval( $subscription_delay ) . ' Days', current_time( 'timestamp' ) ) );
                            }
                        } else {
                            // $start_date = current_time( 'mysql' );
                            $start_date = NULL;
                        }

                        // If we are tweaking the start date via Subscription Delays Add On, let's set that to the subscription.
                        if ( ! empty( $start_date ) ) {
                            $body['start_date'] = apply_filters( 'pmpro_paystack_subscription_start_date', $start_date );
                        }
                        
                        
                        $args = array(
                            'body'      => json_encode($body),
                            'headers'   => $headers,
                            'timeout'   => 60
                        );

                        $request = wp_remote_post($subscription_url, $args);
                        if (!is_wp_error($request)) {
                            $paystack_response = json_decode(wp_remote_retrieve_body($request));
                            if ( isset( $paystack_response->data->status ) && 'active' == $paystack_response->data->status ) {
                                $subscription_code = $paystack_response->data->subscription_code;
                                $token = $paystack_response->data->email_token;
                                $morder->subscription_transaction_id = $subscription_code;
                                $morder->subscription_token = $token;
                                $morder->saveOrder();
                            }
                        }
                    }

                    // get discount code     (NOTE: but discount_code isn't set here. How to handle discount codes for PayPal Standard?)
                    $morder->getDiscountCode();
                    if ( ! empty( $morder->discount_code ) ) {
                        // update membership level
                        $morder->getMembershipLevel( true );
                        $discount_code_id = $morder->discount_code->id;
                    } else {
                        $discount_code_id = '';
                    }

                    // Get the expiration date.
                    if ( ! empty( $morder->membership_level->expiration_number ) ) {
                        $enddate = "'" . date_i18n( "Y-m-d", strtotime( "+ " . $morder->membership_level->expiration_number . " " . $morder->membership_level->expiration_period, current_time( "timestamp" ) ) ) . "'";
                    } else {
                        $enddate = "NULL";
                    }

                    $custom_level = array(
                            'user_id'           => $morder->user_id,
                            'membership_id'     => $morder->membership_level->id,
                            'code_id'           => $discount_code_id,
                            'initial_payment'   => $morder->membership_level->initial_payment,
                            'billing_amount'    => $morder->membership_level->billing_amount,
                            'cycle_number'      => $morder->membership_level->cycle_number,
                            'cycle_period'      => $morder->membership_level->cycle_period,
                            'billing_limit'     => $morder->membership_level->billing_limit,
                            'trial_amount'      => $morder->membership_level->trial_amount,
                            'trial_limit'       => $morder->membership_level->trial_limit,
                            'startdate'         => $startdate,
                            'enddate'           => $enddate
                        );
                    if ($morder->status != 'success') {
                         
                        if (pmpro_changeMembershipLevel($custom_level, $morder->user_id, 'changed')) {
                            $morder->membership_id = $morder->membership_level->id;
                            $morder->payment_transaction_id = $webhook_reference_id;
                            $morder->status = "success";
                            $morder->saveOrder();
                        }

                    }
                }
            }
        }
    }
}

/**
 * Handle the successful recurring payments.
 *
 * @param object $post_event The Paystack event object data.
 * @return void
 */
function pmpro_paystack_renew_payment( $post_event ) {
    if ( isset( $post_event->data->paid ) && ( $post_event->data->paid == 1 ) ) {
        $amount = $post_event->data->subscription->amount/100;
        $subscription_code = $post_event->data->subscription->subscription_code;
        $email = $post_event->data->customer->email;
        $old_order = new MemberOrder();
        $old_order->getLastMemberOrderBySubscriptionTransactionID( $subscription_code );
       
        if ( empty( $old_order ) || empty( $old_order->id ) ) {
            pmpro_paystack_webhook_log( 'Could not find last order for subscription code: ' . $subscription_code );
            pmpro_paystack_webhook_exit();
        }

        $user_id = $old_order->user_id;
        $user = get_userdata( $user_id );

        if ( empty( $user ) ) {
            pmpro_paystack_webhook_log( 'Could not get user for renewal payment' );
            pmpro_paystack_webhook_exit();
        }

        // Let's create the order.
        $morder = new MemberOrder();
        $morder->user_id = $old_order->user_id;
        $morder->membership_id = $old_order->membership_id;
        $morder->subtotal = $amount;
        $morder->total = $amount;
        $morder->payment_transaction_id = ! empty( $post_event->data->invoice_code ) ? $post_event->data->invoice_code : '';
        $morder->subscription_transaction_id = $subscription_code;
         // Set the orders date to time it was paid.
         if ( ! empty( $post_event->data->paid_at ) ) {
            $morder->timestamp = strtotime( sanitize_text_field( $post_event->data->paid_at ) );
        }
        $morder->gateway = $old_order->gateway;
        $morder->gateway_environment = $old_order->gateway_environment;

        // Save entire order data to IPN Log - loop through the order object.
        $order_data = array();
        foreach ( $morder as $key => $value ) {
            $order_data[ $key ] = $value;
        }

        pmpro_paystack_webhook_log( 'Order data: ' . print_r( $order_data, true ) );

        $morder->status = 'success';
        $morder->saveOrder();
        $morder->getMemberOrderByID($morder->id);

        //email the user their invoice
        $pmproemail = new PMProEmail();
        $pmproemail->sendInvoiceEmail( $user, $morder );

        do_action('pmpro_subscription_payment_completed', $morder);
        pmpro_paystack_webhook_log( sprintf( 'Subscription payment completed for user with ID: %d. Order ID: %s', $user_id, $morder->code ) );
        pmpro_paystack_webhook_exit();
    }
}