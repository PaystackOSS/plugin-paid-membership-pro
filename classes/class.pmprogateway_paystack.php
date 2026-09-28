<?php
/**
 * Paystack Gateway Class for Paid Memberships Pro.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once PMPRO_DIR . '/classes/gateways/class.pmprogateway.php';

//load classes init method
add_action( 'init', array( 'PMProGateway_paystack', 'init' ) );

class PMProGateway_paystack extends PMProGateway {

	function __construct( $gateway = NULL ) {
		$this->gateway = $gateway;
		return $this->gateway;
	}

	/**
	 * Run on WP init.
	 * This method will run all the necessary gateway hooks that are needed.
	 */
	public static function init() {

		// Make sure Paystack is a gateway option.
		add_filter( 'pmpro_gateways', array( 'PMProGateway_paystack', 'pmpro_gateways' ) );

		// Add fields to payment settings.
		add_filter( 'pmpro_payment_options', array( 'PMProGateway_Paystack', 'pmpro_payment_options' ) );
		add_filter( 'pmpro_payment_option_fields', array( 'PMProGateway_Paystack', 'pmpro_payment_option_fields' ), 10, 2 ); 
		add_action( 'wp_ajax_pmpro_paystack_ipn', array( 'PMProGateway_Paystack', 'pmpro_paystack_ipn' ) );
		add_action( 'wp_ajax_nopriv_pmpro_paystack_ipn', array( 'PMProGateway_Paystack', 'pmpro_paystack_ipn' ) );

		// Keeping the deprecated action for backwards compatibility.
		add_action( 'wp_ajax_kkd_pmpro_paystack_ipn', array( 'PMProGateway_Paystack', 'kkd_pmpro_paystack_ipn' ) );
		add_action( 'wp_ajax_nopriv_kkd_pmpro_paystack_ipn', array( 'PMProGateway_Paystack', 'kkd_pmpro_paystack_ipn' ) );

		// Adjust the confirmation message when waiting for Paystack to process the payment.
		add_filter( 'pmpro_confirmation_payment_incomplete_message', array( 'PMProGateway_Paystack', 'pmpro_confirmation_incomplete_message' ), 10, 2 );

		$gateway = pmpro_getGateway();
		if ( $gateway == 'paystack' ) {
			global $pmpro_gateway_ready;
			$pmpro_gateway_ready = true;

			add_filter( 'pmpro_include_billing_address_fields', '__return_false' );
			add_filter( 'pmpro_include_payment_information_fields', '__return_false' );
			add_filter( 'pmpro_billing_show_payment_method', '__return_false' );
			add_filter( 'pmpro_required_billing_fields', array( 'PMProGateway_Paystack', 'pmpro_required_billing_fields' ) );

			// Refund functionality.
			add_filter( 'pmpro_allowed_refunds_gateways', array( 'PMProGateway_Paystack', 'pmpro_allowed_refunds_gateways' ) );
			add_filter( 'pmpro_process_refund_paystack', array( 'PMProGateway_Paystack', 'process_refund' ), 10, 2 );
		}

	}

    /**
     * Add Paystack to the gateway list for PMPro
     */
	public static function pmpro_gateways( $gateways ) {
		if ( empty( $gateways['paystack'] ) ) {
			$gateways['paystack'] = __( 'Paystack', 'text-domain' );
		}

		return $gateways;
	}

     /**
     * Wrapper function for newly named function instead to be more inline with PMPro naming conventions.
     * DEPRECATED use pmpro_paystack_ipn instead.
     * @since 1.0
     */
    static function kkd_pmpro_paystack_ipn() {
        self::pmpro_paystack_ipn();
    }

    /**
     * Webhook handler for Paystack.
     * @since 1.0 (Renamed in 1.7.1)
     */
    static function pmpro_paystack_ipn() {
        require_once PMPRO_PAYSTACK_DIR . 'services/paystack-webhook.php';
        exit;
    }

    /**
     * Check whether or not a gateway supports a specific feature.
     *
     * @param string $feature The feature we need to check if it is supported.
     * @return string|boolean $supports In some cases, we may need to return strings for the feature or a boolean value if it's supported or not.
     */
    public static function supports( $feature ) {
        $supports = array(
            'subscription_sync' => true,
            'payment_method_updates' => false
        );

        if ( empty( $supports[$feature] ) ) {
            return false;
        }

        return $supports[$feature];
    }

    /**
     * Get a list of payment options that the Paystack gateway needs/supports.
     */
    static function getGatewayOptions() {
        $options = array (
            'paystack_tsk',
            'paystack_tpk',
            'paystack_lsk',
            'paystack_lpk',
            'currency',
            'tax_state',
            'tax_rate'
            );

        return $options;
    }

    /**
     * Set payment options for payment settings page.
	 * This has been deprecated due to Paid Memberships Pro V3.5+.
     */
    static function pmpro_payment_options( $options ) {
        //get Paystack options
        $paystack_options = self::getGatewayOptions();

        //merge with others.
        $options = array_merge( $paystack_options, $options );

        return $options;
    }

    /**
     * Display fields for Paystack options.
	 * This function is deprecated and only used for versions before PMPro 3.5
	 * See the new callback `show_settings_fields` in the `PMProGateway_Paystack` class.
     */
    static function pmpro_payment_option_fields( $values, $gateway ) {
        ?>
        <tr class="gateway gateway_paystack" <?php if($gateway != "paystack") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label for="paystack_tsk"><?php esc_html_e('Test Secret Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
            </th>
            <td>
                <input type="text" id="paystack_tsk" name="paystack_tsk" size="60" value="<?php echo esc_attr($values['paystack_tsk'])?>" />
            </td>
        </tr>
        <tr class="gateway gateway_paystack" <?php if($gateway != "paystack") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label for="paystack_tpk"><?php esc_html_e('Test Public Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
            </th>
            <td>
                <input type="text" id="paystack_tpk" name="paystack_tpk" size="60" value="<?php echo esc_attr($values['paystack_tpk'])?>" />
            </td>
        </tr>
        <tr class="gateway gateway_paystack" <?php if($gateway != "paystack") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label for="paystack_lsk"><?php esc_html_e('Live Secret Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
            </th>
            <td>
                <input type="text" id="paystack_lsk" name="paystack_lsk" size="60" value="<?php echo esc_attr($values['paystack_lsk'])?>" />
            </td>
        </tr>
        <tr class="gateway gateway_paystack" <?php if($gateway != "paystack") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label for="paystack_lpk"><?php esc_html_e('Live Public Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
            </th>
            <td>
                <input type="text" id="paystack_lpk" name="paystack_lpk" size="60" value="<?php echo esc_attr($values['paystack_lpk'])?>" />
            </td>
        </tr>
            <tr class="gateway gateway_paystack" <?php if($gateway != "paystack") { ?>style="display: none;"<?php } ?>>
            <th scope="row" valign="top">
                <label><?php esc_html_e('Webhook', 'paystack-gateway-paid-memberships-pro');?>:</label>
            </th>
            <td>
                <p><?php esc_html_e('To fully integrate with Paystack, be sure to use the following for your Webhook URL to', 'paystack-gateway-paid-memberships-pro');?><br/><code><?php echo esc_html( admin_url("admin-ajax.php") . "?action=pmpro_paystack_ipn" );?></code></p>

            </td>
        </tr>
        <?php
    }

	/**
	 * Show settings fields for Paystack gateway.
	 * 
	 * @since 1.9
	 *
	 */
	static function show_settings_fields() {
		?>
		<p>
				<?php
					printf(
						/* translators: %s: URL to the Paystack gateway documentation. */
						esc_html__( 'For detailed setup instructions, please visit our %s.', 'paystack-gateway-paid-memberships-pro' ),
						'<a href="https://www.paidmembershipspro.com/add-ons/paystack-gateway/#h-setup?utm_source=plugin&utm_medium=pmpro-paymentsettings&utm_campaign=documentation&utm_content=paystack-documentation" target="_blank">' . esc_html__( 'Paystack documentation', 'paystack-gateway-paid-memberships-pro' ) . '</a>'
					);
				?>
			</p>
			<div id="pmpro_paystack" class="pmpro_section" data-visibility="shown" data-activated="true">
				<div class="pmpro_section_toggle">
					<button class="pmpro_section-toggle-button" type="button" aria-expanded="true">
						<span class="dashicons dashicons-arrow-up-alt2"></span>
						<?php esc_html_e( 'Settings', 'paystack-gateway-paid-memberships-pro' ); ?>
					</button>
				</div>
				<div class="pmpro_section_inside">
					<table class="form-table">
						<tbody>
							<tr class="gateway gateway_paystack">
								<th scope="row" valign="top">
									<label for="paystack_tsk"><?php esc_html_e('Test Secret Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
								</th>
								<td>
									<input type="text" id="paystack_tsk" name="paystack_tsk" size="60" value="<?php echo esc_attr( get_option('pmpro_paystack_tsk') ); ?>" />
								</td>
							</tr>
							<tr class="gateway gateway_paystack">
								<th scope="row" valign="top">
									<label for="paystack_tpk"><?php esc_html_e('Test Public Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
								</th>
								<td>
									<input type="text" id="paystack_tpk" name="paystack_tpk" size="60" value="<?php echo esc_attr(get_option( 'pmpro_paystack_tpk' ) ); ?>" />
								</td>
							</tr>
							<tr class="gateway gateway_paystack">
								<th scope="row" valign="top">
									<label for="paystack_lsk"><?php esc_html_e('Live Secret Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
								</th>
								<td>
									<input type="text" id="paystack_lsk" name="paystack_lsk" size="60" value="<?php echo esc_attr(get_option( 'pmpro_paystack_lsk' ) ); ?>" />
								</td>
							</tr>
							<tr class="gateway gateway_paystack">
								<th scope="row" valign="top">
									<label for="paystack_lpk"><?php esc_html_e('Live Public Key', 'paystack-gateway-paid-memberships-pro');?>:</label>
								</th>
								<td>
									<input type="text" id="paystack_lpk" name="paystack_lpk" size="60" value="<?php echo esc_attr(get_option( 'pmpro_paystack_lpk' ) ); ?>" />
								</td>
							</tr>
							<tr class="gateway gateway_paystack">
								<th scope="row" valign="top">
									<label><?php esc_html_e('Webhook', 'paystack-gateway-paid-memberships-pro');?>:</label>
								</th>
								<td>
									<p><?php esc_html_e( 'To fully integrate with Paystack, be sure to use the following for your Webhook URL to', 'paystack-gateway-paid-memberships-pro' );?><br/><code><?php echo esc_html( admin_url("admin-ajax.php") . "?action=pmpro_paystack_ipn" );?></code></p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		<?php
	}

	/**
	 * Get a description for this gateway.
	 *
	 * @since 1.9
	 *
	 * @return string
	 */
	public static function get_description_for_gateway_settings() {
		return esc_html__( 'With Paystack, members can pay using credit or debit cards, bank transfers, USSD, mobile money, QR codes, Apple Pay and more. Paystack is accepted worldwide and offers multi-currency support across numerous African countries.', 'paystack-gateway-paid-memberships-pro' );
	}

	/**
	 * Save settings fields for Paystack the gateway.
	 * 
	 * @since 1.9
	 */
	public static function save_settings_fields() {
			$settings_to_save = array(
				'paystack_tsk',
				'paystack_tpk',
				'paystack_lsk',
				'paystack_lpk',
			);

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Called from PMPro's adminpages/paymentsettings.php after the capability check and check_admin_referer( 'savesettings', 'pmpro_paymentsettings_nonce' ).
			foreach ( $settings_to_save as $setting ) {
				if ( isset( $_REQUEST[ $setting ] ) ) {
					update_option( 'pmpro_' . $setting, sanitize_text_field( wp_unslash( $_REQUEST[ $setting ] ) ) );
				}
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

    /**
     * Remove required billing fields from checkout.
     */
    static function pmpro_required_billing_fields( $fields ) {
        unset( $fields['bfirstname'] );
        unset( $fields['blastname'] );
        unset( $fields['baddress1'] );
        unset( $fields['bcity'] );
        unset( $fields['bstate'] );
        unset( $fields['bzipcode'] );
        unset( $fields['bphone'] );
        unset( $fields['bemail'] );
        unset( $fields['bcountry'] );
        unset( $fields['CardType'] );
        unset( $fields['AccountNumber'] );
        unset( $fields['ExpirationMonth'] );
        unset( $fields['ExpirationYear'] );
        unset( $fields['CVV'] );

        return $fields;
    }

    function process( &$order ) {

        if ( empty( $order->code ) ) {
			$order->code = $order->getRandomCode();
		}

		// clean up some values and save the token order so we can complete it later.
		$order->payment_type = 'Paystack';
		$order->CardType     = '';
		$order->cardtype     = '';
		$order->status = 'token';
		$order->saveOrder();

		pmpro_save_checkout_data_to_order( $order );

		do_action( 'pmpro_before_send_to_paystack', $order->user_id, $order );

        $this->sendToPaystack( $order );
    }

    /**
     * Redirect to Paystack to charge the payment.
     */

     /// Todo: Refactor using newew order code methods.
    function sendToPaystack( &$order ) {
        global $pmpro_currency;

        // Use this filter for other Add On compatibility that may use this filter.
        do_action( 'pmpro_paypalexpress_session_vars' );

        $params = array();
        $amount = $order->PaymentAmount; // This must change as well.
        $amount_tax = $order->getTaxForPrice($amount);
        $amount = round((float)$amount + (float)$amount_tax, 2);

        $amount = floatval($order->InitialPayment); /// This must change.

        $mode = pmpro_getOption("gateway_environment");
        if ($mode == 'sandbox') {
            $key = pmpro_getOption("paystack_tsk");
            $pk = pmpro_getOption("paystack_tpk");
        } else {
            $key = pmpro_getOption("paystack_lsk");
            $pk = pmpro_getOption("paystack_lpk");
        }
        if ($key  == '') {
            echo "Api keys not set";
        }

        $koboamount = $amount*100;


        $paystack_url = 'https://api.paystack.co/transaction/initialize';
        $headers = array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer '.$key
        );
      
        //Create Plan
        $body = array(
            'email'        => $order->Email,
            'amount'       => $koboamount,
            'reference'    => $order->code,
            'currency'     => $pmpro_currency,
            'callback_url' => pmpro_url("confirmation", "?level=" . $order->membership_level->id),
            'metadata' => json_encode(array('custom_fields' => array(
                array(
                    "display_name"=>"Plugin",
                    "variable_name"=>"plugin",
                    "value"=>"pm-pro"
                ),
                
            ), 'custom_filters' => array("recurring" => true))),
        ); 

        // If the level is recurring only allow card payments for the subscription as other methods don't work.
        $level = $order->getMembershipLevel();
        if ( pmpro_isLevelRecurring( $level ) ) {
            $body['channels'] = array( 'card' );
        }

        $args = array(
            'body'      => json_encode($body),
            'headers'   => $headers,
            'timeout'   => 60
        );

        $request = wp_remote_post( $paystack_url, $args );
        // print_r($request);
        if (!is_wp_error($request)) {
            $paystack_response = json_decode( wp_remote_retrieve_body($request ));
            if ($paystack_response->status){
                $url = $paystack_response->data->authorization_url;
                wp_redirect($url); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Offsite redirect to the Paystack hosted payment page.
                exit;
            } else {
                wp_redirect(pmpro_url("checkout", "?level=" . $order->membership_level->id . "&error=" . $paystack_response->message)); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- pmpro_url() is filterable and may point offsite (e.g. Network Subsite).
                exit();
            }
        } else {
            wp_redirect(pmpro_url("checkout", "?level=" . $order->membership_level->id . "&error=Failed")); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- pmpro_url() is filterable and may point offsite (e.g. Network Subsite).
            exit();
        }
        exit;
    }

    /**
     * Allow "Sync" with gateway for subscriptions.
     * @since 1.7.2
     */
    public function update_subscription_info( $subscription ) {
        $subscription_id = $subscription->get_subscription_transaction_id();
        $backtrace = self::get_caller_info();
        $furtherbacktrace = wp_debug_backtrace_summary();

        $mode = get_option( 'pmpro_gateway_environment');
        if ( $mode == 'sandbox' ) {
            $key = get_option( 'pmpro_paystack_tsk' );
        } else {
            $key = get_option( 'pmpro_paystack_lsk' );
        }

        $paystack_url = 'https://api.paystack.co/subscription/' . $subscription_id;
        
        $headers = array(
            'Authorization' => 'Bearer ' . $key
        );

        $args = array(
            'headers' => $headers,
            'timeout' => 60,
        );
        
        $request = wp_remote_get( $paystack_url, $args );

        // Request is okay, so let's get the data now and update what we need to.
        if ( ! is_wp_error( $request ) ) {
            $response = json_decode( wp_remote_retrieve_body( $request ) );

            if ( 200 !== wp_remote_retrieve_response_code( $request ) )  {
                // Throw an error here from the API
                return sprintf( esc_html__( 'Paystack error: %s', 'paystack-gateway-paid-memberships-pro' ), $response->message );
            }
            
            $update_array = array();
            $sub_info = $response->data;

            // The response status isn't active, so we're most likely already cancelled.
            if ( $sub_info->status !== 'active' ) {
                $update_array['status'] = 'cancelled'; // Does it 
            } else {
                $update_array['status'] = 'active';
            }
            

            // Let's make sure the cycle_numbers are correctly set based on the interval from Paystack.
            switch( $sub_info->plan->interval ) {
                case 'quarterly':
                    $update_array['cycle_number'] = 3;
                    break;
                case 'biannually':
                    $update_array['cycle_number'] = 6;
                    break;
            }

            // Update the subscription.
            $update_array['next_payment_date'] = sanitize_text_field( $sub_info->next_payment_date ); // [YYYY]-[MM]-[DD
            $update_array['startdate'] = sanitize_text_field( $sub_info->createdAt ); 
            $update_array['billing_amount'] = (float) $sub_info->amount/100; // Get currency value
            $update_array['cycle_period'] = $this->convert_interval_for_pmpro( $sub_info->plan->interval ); // Convert interval for PMPro format (which sanitizes it)
            $subscription->set( $update_array );
        } else {
            return esc_html__( 'There was an error communicating with Paystack. Please confirm your connectivity and API details and try again.', 'paystack-gateway-paid-memberships-pro' );
        }
    }

    function cancel(&$order, $update_status = true )
    {
        $backtrace = self::get_caller_info();
        $furtherbacktrace = wp_debug_backtrace_summary();
      
        //no matter what happens below, we're going to cancel the order in our system
        if ( $update_status ) {
            $order->updateStatus( "cancelled" );
        }

        $mode = pmpro_getOption("gateway_environment");
        $code = $order->subscription_transaction_id;
        if ($mode == 'sandbox') {
            $key = pmpro_getOption("paystack_tsk");
        } else {
            $key = pmpro_getOption("paystack_lsk");

        }

        if ( $code != "") {
            $paystack_url = 'https://api.paystack.co/subscription/' . $code;
           
            $headers = array(
                'Authorization' => 'Bearer ' . $key
            );
            $args = array(
                'headers' => $headers,
                'timeout' => 60,
            );
            
            $request = wp_remote_get($paystack_url, $args);
            if (!is_wp_error($request) && 200 == wp_remote_retrieve_response_code($request)) {
                $paystack_response = json_decode(wp_remote_retrieve_body($request));
                if ('active' == $paystack_response->data->status && $code == $paystack_response->data->subscription_code && '1' == $paystack_response->status) {

                    $paystack_url = 'https://api.paystack.co/subscription/disable';
                    $headers = array(
                        'Content-Type'  => 'application/json',
                        'Authorization' => "Bearer ".$key
                    );
                    $body = array(
                        'code'  => $paystack_response->data->subscription_code,
                        'token' => $paystack_response->data->email_token,
                        'debug_trace'=> $backtrace . " ". $furtherbacktrace
                    );
                    $args = array(
                        'body'      => json_encode($body),
                        'headers'   => $headers,
                        'timeout'   => 60,
                    );

                    $request = wp_remote_post($paystack_url, $args);

                    if ( ! is_wp_error( $request ) ) {
                        return true;
                    } else {
                        return false; // There was an error cancelling for some reason.
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * Allow refunds from within Paid Memberships Pro and Paystack.
     * @since 1.7.3
     */
    public static function process_refund( $success, $order ) {
        global $current_user;

        //default to using the payment id from the order
        if ( !empty( $order->payment_transaction_id ) ) {
            $transaction_id = $order->payment_transaction_id;
        }

        //need a transaction id
        if ( empty( $transaction_id ) ) {
            return false;
        }

        // OKAY do the refund now.
        // Make the API call to PayStack to refund the order.
            $mode = pmpro_getOption("gateway_environment");
            if ( $mode == "sandbox" ) {
                $key = pmpro_getOption("paystack_tsk");
                
            } else {
                $key = pmpro_getOption("paystack_lsk");
            }

            $paystack_url = 'https://api.paystack.co/refund/';
            
            $headers = array(
                'Authorization' => 'Bearer ' . $key,
                'Cache-Control' => 'no-cache'
            );

            // The transaction ID for the refund.
            $fields = array(
                'transaction' => $transaction_id
            );

            $args = array(
                'headers' => $headers,
                'timeout' => 60,
                'body' => $fields
            );

            $success = false;

            // Try to make the API call now.
            $request = wp_remote_post( $paystack_url, $args );

            if ( ! is_wp_error( $request ) ) {

                $response = json_decode( wp_remote_retrieve_body( $request ) );

                // If not successful throw an error.
                if ( ! $response->status ) {
                    $order->notes = trim( $order->notes.' '.sprintf( __('Admin: Order refund failed on %1$s for transaction ID %2$s by %3$s. Order may have already been refunded.', 'paystack-gateway-paid-memberships-pro' ), date_i18n('Y-m-d H:i:s'), $transaction_id, $current_user->display_name ) );
                    $order->saveOrder();
                } else {
                // Set the order status to refunded and save it and return true
                $order->status = 'refunded';
                
                $success = true;

                $order->notes = trim( $order->notes.' '.sprintf( __('Admin: Order successfully refunded on %1$s for transaction ID %2$s by %3$s.', 'paystack-gateway-paid-memberships-pro' ), date_i18n('Y-m-d H:i:s'), $transaction_id, $current_user->display_name ) );	

                $user = get_user_by( 'id', $order->user_id );
                //send an email to the member
                $myemail = new PMProEmail();
                $myemail->sendRefundedEmail( $user, $order );

                //send an email to the admin
                $myemail = new PMProEmail();
                $myemail->sendRefundedAdminEmail( $user, $order );

                $order->saveOrder();
                }                            
            }

            return $success;
    }

    /** 
     * Enable refund functionality for paystack.
     * @since 1.7.3
     */
    static function pmpro_allowed_refunds_gateways( $gateways ) {
        $gateways[] = 'paystack';
        return $gateways;
    }

    /**
     * Change the confirmation message, as Paystack's webhook notification may take a few seconds.
     * @since 1.9
     */
    public static function pmpro_confirmation_incomplete_message( $message, $pmpro_invoice ) {
        
        if ( $pmpro_invoice->gateway != 'paystack' ) {
            return $message;
        }

        $message .= ' ' . esc_html__( 'This may take a few seconds.', 'text-domain' );

        return $message;
    }
    
     /**
     * Undocumented function
     *
     * @param string $interval The pmpro paystack
     * @return string $interval The required interval for PayStack to recognize.
     */
    function convert_interval_for_paystack( $interval ) {

        $interval = strtolower( $interval );

        switch( $interval ) {
            case 'day':
                $interval = 'daily';
                break;
            case 'week':
                $interval = 'weekly';
                break;
            case 'month':
                $interval = 'monthly';
                break;
            case 'year':
                $interval = 'annually';
                break;
            default:
                $interval = 'monthly';
        }

        return $interval;

    }

    /**
     * Convert Paystack's intervals for PMPro's format.
     *
     * @param string $interval The received Paystack interval (i.e. Weekly, Monthly etc )
     * @return string $interval The converted interval for PMPro.
     */
    function convert_interval_for_pmpro( $interval ) {

        $interval = strtolower( $interval );

        switch( $interval ) {
            case 'daily':
                $interval = 'Day';
                break;
            case 'weekly':
                $interval = 'Week';
                break;
            case 'monthly':
                $interval = 'Month';
                break;
            case 'annually':
                $interval = 'Year';
                break;
            case 'quarterly':
                $interval = 'Month';
                break;
            case 'biannually':
                $interval = 'Month';
                break;
            default:
                $interval = 'Month';
        }

        return $interval;

    }

    // Get Caller info for debugging.
    function get_caller_info() {
        $c = '';
        $file = '';
        $func = '';
        $class = '';
        $trace = debug_backtrace();
        if (isset($trace[2])) {
            $file = $trace[1]['file'];
            $func = $trace[2]['function'];
            if ((substr($func, 0, 7) == 'include') || (substr($func, 0, 7) == 'require')) {
                $func = '';
            }
        } else if (isset($trace[1])) {
            $file = $trace[1]['file'];
            $func = '';
        }
        if (isset($trace[3]['class'])) {
            $class = $trace[3]['class'];
            $func = $trace[3]['function'];
            $file = $trace[2]['file'];
        } else if (isset($trace[2]['class'])) {
            $class = $trace[2]['class'];
            $func = $trace[2]['function'];
            $file = $trace[1]['file'];
        }
        if ($file != '') $file = basename($file);
        $c = $file . ": ";
        $c .= ($class != '') ? ":" . $class . "->" : "";
        $c .= ($func != '') ? $func . "(): " : "";
        return($c);
    }

} // End of class PMProGateway_Paystack


/// Probably needs to go elsewhere?
/**
 * Create the log string for debugging purposes.
 *
 * @param string $s The error/information you want to log to the IPN log.
 * @return string $logstr A formatted message for the logfile.
 * 
 * @since 1.7.5
 */
function pmpro_paystack_webhook_log( $s ) {
    global $logstr;
	$logstr .= "\t" . $s . "\n";
}

/**
 * Write to the log file and exit.
 *
 * @since 1.7.5
 */
function pmpro_paystack_webhook_exit() {
    global $logstr;

	//for log
	if ( $logstr ) {
		$logstr = "Logged On: " . date_i18n( "m/d/Y H:i:s" ) . "\n" . $logstr . "\n-------------\n";

		echo esc_html( $logstr );

		//log or dont log? log in file or email?
		//- dont log if constant is undefined or defined but false
		//- log to file if constant is set to TRUE or 'log'
		//- log to file if constant is defined to a valid email address
		if ( defined( 'PMPRO_PAYSTACK_WEBHOOK_DEBUG' ) ) {
			if( PMPRO_PAYSTACK_WEBHOOK_DEBUG === false ){
				//dont log here. false mean no.
				//should avoid counterintuitive interpretation of false.
			} elseif ( PMPRO_PAYSTACK_WEBHOOK_DEBUG === "log" ) {
				//file
            
				$logfile = apply_filters( 'pmpro_paystack_webhook_log_file', PMPRO_PAYSTACK_DIR . "logs/ipn.txt" );

                // Check if the dir exists, if not let's create it.
                $logdir = dirname( $logfile );
                if ( ! file_exists( $logdir ) ) {
                    mkdir( $logdir, 0775 );
                }

                // If the log file doesn't exist let's create it.
                if ( ! file_exists( $logfile ) ) {
                    // Create a blank logfile
                    file_put_contents( $logfile, "" );
                }

				$loghandle = fopen( $logfile, "a+" );
				fwrite( $loghandle, $logstr );
				fclose( $loghandle );
			} elseif ( is_email( PMPRO_PAYSTACK_WEBHOOK_DEBUG ) ) {
				//email to specified address
				wp_mail( PMPRO_PAYSTACK_WEBHOOK_DEBUG, get_option( "blogname" ) . " IPN Log", nl2br( esc_html( $logstr ) ) );							
			} else {
				//email to admin
				wp_mail( get_option( "admin_email" ), get_option( "blogname" ) . " IPN Log", nl2br( esc_html( $logstr ) ) );							
			}
		}
	}

	exit;

}
