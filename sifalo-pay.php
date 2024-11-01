<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              sifalo.com
 * @since             1.0.0
 * @package


 *
 * @wordpress-plugin
 * Plugin Name:       Sifalo Pay
 * Plugin URI:        pay.sifalo.com
 * Description:       Accept eWallet Payments ( EVC, Zaad, Sahal, eDahab, Premier Wallet, Mastercard, Visa, AmericanExpress...etc ) securely on your online store.
 * Version:           2.1.2
 * Author:            Sifalo Technologies
 * Author URI:        https://pay.sifalo.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sifalo-pay
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

define('SIFALO_PAY_VERSION', '2.1.2');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sifalo-pay-activator.php
 */
function activate_sifalo_pay()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-sifalo-pay-activator.php';
	Sifalo_Pay_Activator::activate();

	$sifalo_pay_api_user 	= get_option('sifalo_pay_api_user');
	$sifalo_pay_api_key 	= get_option('sifalo_pay_api_key');
    
    if( empty( $sifalo_pay_api_user ) && empty( $sifalo_pay_api_key ) )
    {
		iscu_default_settings();
    }
}

function iscu_default_settings(){

	$iscu_payment_options 	= array();
	$api_user_id 			= '';
	$api_key 				= '';
	$iscu_zes_pay_settings 				= get_option( 'woocommerce_zes_pay_settings' );
	$iscu_edahab_pay_settings 			= get_option( 'woocommerce_edahab_pay_settings' );
	$iscu_premier_wallet_pay_settings	= get_option( 'woocommerce_premier_wallet_pay_settings' );
	$iscu_card_pay_settings 			= get_option( 'woocommerce_card_pay_settings' );
	$woocommerce_sifalo_pay_settings 	= get_option( 'woocommerce_sifalo_pay_settings' );
	$iscu_payment_options[]				= $iscu_zes_pay_settings;
	$iscu_payment_options[]				= $iscu_edahab_pay_settings;
	$iscu_payment_options[]				= $iscu_premier_wallet_pay_settings;
	$iscu_payment_options[]				= $iscu_card_pay_settings;
	$iscu_payment_options[]				= $woocommerce_sifalo_pay_settings;
	$api_user_id 						= '';
	$api_key 	 						= '';

	foreach( $iscu_payment_options as $iscu_payment_option ){

		if( !empty( $iscu_payment_option['api_user_id'] ) && !empty( $iscu_payment_option['api_key'] ) )
		{
			$api_user_id = $iscu_payment_option['api_user_id'];
			$api_key 	 = $iscu_payment_option['api_key'];
			break;
		}
	}
	
	$iscu_zes_pay_settings['api_user_id'] 				= $api_user_id;
	$iscu_zes_pay_settings['api_key'] 					= $api_key;
	
	$iscu_edahab_pay_settings['api_user_id'] 			= $api_user_id;
	$iscu_edahab_pay_settings['api_key'] 				= $api_key;
	
	$iscu_premier_wallet_pay_settings['api_user_id'] 	= $api_user_id;
	$iscu_premier_wallet_pay_settings['api_key'] 		= $api_key;
	
	$iscu_card_pay_settings['api_user_id'] 				= $api_user_id;
	$iscu_card_pay_settings['api_key']					= $api_key;
	
	$iscu_card_pay_settings['api_environment'] 			= 'https://api.sifalopay.com/gateway/';

	update_option( 'sifalo_pay_api_user', $api_user_id );
	update_option( 'sifalo_pay_api_key', $api_key );

	$iscu_zes_pay_settings['enabled'] 				= 'yes';
	$iscu_edahab_pay_settings['enabled'] 			= 'yes';
	$iscu_premier_wallet_pay_settings['enabled'] 	= 'yes';
	$iscu_card_pay_settings['enabled']				= 'yes';
	update_option( 'woocommerce_zes_pay_settings', $iscu_zes_pay_settings );
	update_option( 'woocommerce_edahab_pay_settings', $iscu_edahab_pay_settings );
	update_option( 'woocommerce_premier_wallet_pay_settings', $iscu_premier_wallet_pay_settings );
	update_option( 'woocommerce_card_pay_settings', $iscu_card_pay_settings );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sifalo-pay-deactivator.php
 */
function deactivate_sifalo_pay()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-sifalo-pay-deactivator.php';
	Sifalo_Pay_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_sifalo_pay');
register_deactivation_hook(__FILE__, 'deactivate_sifalo_pay');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-sifalo-pay.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
add_action('plugins_loaded', 'sifalo_pay_init');
function sifalo_pay_init()
{
	if (!class_exists('WC_Payment_Gateway')) return;

	class WC_Gateway_Sifalo_Pay extends WC_Payment_Gateway
	{
		public function __construct()
		{

			$this->id                 = 'sifalo_pay';

			$this->has_fields         = false;

			$this->method_title       = __('Sifalo Pay', 'woocommerce');

			$this->method_description = __('Enable customers to transfer funds via eWallet Systems ( EVC, ZAAD, SAHAL, eDahab or Premier Wallet ).');
			// load the settings
			$this->init_form_fields();
			$this->init_settings();
			// Define variables set by the user in the admin section

			$this->title            = $this->get_option('title');

			$this->description      = $this->get_option('description');

			$this->instructions     = $this->get_option('instructions', $this->description);

			$_SESSION['api_user_id'] = $this->get_option('api_user_id');
			$_SESSION['api_key'] = $this->get_option('api_key');

			// fill the rest
			if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			} else {
				add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
			}
		}
		public function init_form_fields()
		{

			$this->form_fields = array(

				'enabled' => array(

					'title'   => __('Enable/Disable', 'woocommerce'),

					'type'    => 'checkbox',

					'label'   => __('Enable Sifalo Pay', 'woocommerce'),

					'default' => 'yes'

				),

				'title' => array(

					'title'       => __('Title', 'woocommerce'),

					'type'        => 'text',

					'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),

					'default'     => __('Sifalo Pay', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

					

				),

				'description' => array(

					'title'       => __('Description', 'woocommerce'),

					'type'        => 'textarea',

					'description' => __('Payment method description that the customer will see on your checkout.', 'woocommerce'),

					'default'     => __('Pay via ZAAD, EVC, SAHAL, eDahab or Premier Wallet using Sifalo Pay'),

					'desc_tip'    => true,

				),

				'instructions' => array(

					'title'       => __('Instructions', 'woocommerce'),

					'type'        => 'textarea',

					'description' => __('Instructions that will be added to the thank you page and emails.', 'woocommerce'),

					'default'     => __('Pay via ZAAD, EVC, SAHAL, eDahab or Premier Wallet using Sifalo Pay', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

				),

				'api_user_id' => array(

					'title'       =>  __('API User', 'woocommerce'),

					'description' => __('You can get this by logging in to your Sifalo Pay account or Contact support@sifalo.com', 'woocommerce'),

					'type'        => 'text',
					
					'desc_tip'    => true,

				),
				'api_key' => array(

					'title'       =>  __('API Key', 'woocommerce'),

					'description' => __('You can get this by logging in to your Sifalo Pay account or Contact support@sifalo.com', 'woocommerce'),

					'type'        => 'password',

					'desc_tip'    => true,

				),

			);
		}
		public function admin_options()
		{

			/*

			     *The heading and paragraph below are the ones that appear on the backend M-PESA settings page

			     */

			echo '<h3>' . 'Sifalo Pay' . '</h3>';



			echo '<p>' . 'eWallet Payments Simplified' . '</p>';



			echo '<table class="form-table">';



			$this->generate_settings_html();



			echo '</table>';
		}

		public function payment_fields() {
			global $woocommerce;

			?>

			<script type="text/javascript">

				(function() {

				// Check if jquery exists
				if(!window.jQuery) {
					return;
				};

				var $ = window.jQuery;

				$(document).ready(function() {

					$('.gateway-selector').change(function(){
						jQuery('body').trigger('update_checkout');
					});

					var gatewaySelector = $('.gateway-selector'),
						zaadField     = $('.zaad_field'),
						edahabField   = $('.edahab_field'),
						pbwalletField = $('.pbwallet_field ');

					// Check that all fields exist
					if(
					!gatewaySelector.length ||
					!zaadField.length ||
					!edahabField.length ||
					!pbwalletField.length
					) {
					return;
					}

					function toggleVisibleFields() {
					var selectedAnswer = gatewaySelector.find(':selected').val();

					if(selectedAnswer === 'zaad') {
						zaadField.show("fast", "swing");
						edahabField.hide();
						pbwalletField.hide();
					} else if(selectedAnswer === 'edahab') {
						zaadField.hide();
						edahabField.show("fast", "swing");
						pbwalletField.hide();
					} else if(selectedAnswer === 'pbwallet') {
						zaadField.hide();
						edahabField.hide();
						pbwalletField.show("fast", "swing");
					} else {
						zaadField.hide();
						edahabField.hide();
						pbwalletField.hide();
					}
					}

					$(document).on('change','input[name=gateway_field]', toggleVisibleFields);
					$(document).on('updated_checkout', toggleVisibleFields);

					toggleVisibleFields();

				});
				})();

				</script>

				<?php
						// display gateway description
						echo esc_html($this->get_option( 'description' )); echo wp_kses('<br/><br/>', array('br'=>array()));

						woocommerce_form_field( 'gateway_field', array(
							'type'            => 'select',
							'required'        => true,
							'class'           => array('gateway-selector', 'form-row-wide'),
							'label'         => __('Choose your preferred payment'),
							'options'         => array(
								'0'			  => '--- CHOOSE PAYMENT ---',
								'zaad'        => 'EVC / ZAAD / SAHAL',
								'edahab'      => 'eDAHAB',
								'pbwallet'    => 'PREMIER WALLET'
							),'default' => '0'
							), WC()->checkout->get_value( 'gateway_field' ) );
	
							echo "<br/>";

							woocommerce_form_field( 'zaad_number', array(
							'type'            => 'text',
							'placeholder'     => 'Enter your EVC or ZAAD number',
							'required'        => true,
							'class'           => array('zaad_field', 'form-row-wide'),
							), WC()->checkout->get_value( 'zaad_number' ) );
	
							woocommerce_form_field( 'edahab_number', array(
							'type'            => 'text',
							'placeholder'     => 'Enter your eDahab number',
							'required'        => true,
							'class'           => array('edahab_field', 'form-row-wide'),
							), WC()->checkout->get_value( 'edahab_number' ) );
	
							woocommerce_form_field( 'pbwallet_number', array(
							'type'            => 'text',
							'placeholder'     => 'Enter your wallet account',
							'required'        => true,
							'class'           => array('pbwallet_field', 'form-row-wide'),
							), WC()->checkout->get_value( 'pbwallet_number' ) );

						

				
	   }

		public	function process_payment($order_id)
		{
			global $woocommerce;

			if(isset($_POST['zaad_number']) && !empty($_POST['zaad_number'])){
				$account= sanitize_text_field($_POST['zaad_number']);
				$gateway = "zaad";
			}elseif(isset($_POST['edahab_number']) && !empty($_POST['edahab_number'])){
				$account = sanitize_text_field($_POST['edahab_number']);
				$gateway = "edahab";
			}elseif(isset($_POST['pbwallet_number']) && !empty($_POST['pbwallet_number'])){
				$account = sanitize_text_field($_POST['pbwallet_number']);
				$gateway = "pbwallet";
			}

			$order = new WC_Order($order_id);
			$orderarray = json_decode($order, true);	
			$billing_address = $orderarray['billing'];
			$currency = $order->get_currency();
			$total_order_amount = $order->order_total;

			// Sifalo Pay payment processing
			$response = $this->process_sifalo_payment($total_order_amount, $order_id, $billing_address, $currency, $account, $gateway);

			if ($response == "success") {
				// Mark as on-hold 
				$order->update_status('processing', __('Payment Recieved', 'woocommerce'));

				// Remove cart
				$woocommerce->cart->empty_cart();

				// Return thankyou redirect
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order)
				);
			}
		}


		public	function process_sifalo_payment($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway)
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class_sifalo_pay_core.php';

			$spay = new sifaloPay(sanitize_text_field($_SESSION['api_user_id']), sanitize_text_field($_SESSION['api_key']));

			$res = $spay->sifaloPay_purchase($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway);

			$this->console_log($total_order_amount);
			if ($res['code'] == '601') {
				//success response
				return "success";
			} else {
				wc_add_notice(  $res['response'], 'error' );
				return "fail";
			}
		}

		public function console_log($output, $with_script_tags = false)
		{
			$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
			if ($with_script_tags) {
				$js_code = '<script>' . $js_code . '</script>';
			}
			echo esc_html($js_code);
		}
	}

	class WC_Gateway_ZES_Pay extends WC_Payment_Gateway
	{
		public function __construct()
		{

			$this->id                 = 'zes_pay';

			$this->has_fields         = false;

			$this->method_title       = __('Sifalo Pay -  ZAAD, EVC, SAHAL ', 'woocommerce');

			$this->method_description = __('Enable customers to transfer funds via eWallet Systems ( EVC, ZAAD, SAHAL ).');
			// load the settings
			$this->init_form_fields();
			$this->init_settings();
			// Define variables set by the user in the admin section

			$this->title            = $this->get_option('title');

			$this->description      = $this->get_option('description');

			$this->instructions     = $this->get_option('instructions', $this->description);

			$_SESSION['api_user_id'] = $this->get_option('api_user_id');
			$_SESSION['api_key'] = $this->get_option('api_key');

			// fill the rest
			if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			} else {
				add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
			}
		}

		public function init_form_fields()
		{

			$this->form_fields = array(

				'enabled' => array(

					'title'   => __('Enable/Disable', 'woocommerce'),

					'type'    => 'checkbox',

					'label'   => __('Enable Sifalo Pay -  ZAAD, EVC, SAHAL ', 'woocommerce'),

					'default' => 'yes'

				),

				'title' => array(

					'title'       => __('Title', 'woocommerce'),

					'type'        => 'text',

					'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),

					'default'     => __('Sifalo Pay -  ZAAD, EVC, SAHAL ', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

					'custom_attributes' => array(
						'readonly' => 'readonly' // Set the field as readonly
					)

				),

				'description' => array(

					'title'       => __('Description', 'woocommerce'),

					'type'        => 'textarea',

					'default'     => __('Pay via Sifalo Pay -  ZAAD, EVC, SAHAL '),

					'desc_tip'    => true,

				),

				'instructions' => array(

					'title'       => __('Instructions', 'woocommerce'),

					'type'        => 'textarea',

					'description' => __('Instructions that will be added to the thank you page and emails.', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

				),

				'api_user_id' => array(

					'title'       =>  __('API User', 'woocommerce'),

					'type'        => 'text',
					
					'desc_tip'    => true,

				),
				'api_key' => array(

					'title'       =>  __('API Key', 'woocommerce'),

					'type'        => 'password',

					'desc_tip'    => true,

				),

			);
		}
		public function admin_options()
		{

			/*

			     *The heading and paragraph below are the ones that appear on the backend M-PESA settings page

			     */

			echo '<h3>' . 'Sifalo Pay -  ZAAD, EVC, SAHAL ' . '</h3>';



			echo '<p>' . 'eWallet Payments Simplified' . '</p>';



			echo '<table class="form-table">';



			$this->generate_settings_html();



			echo '</table>';
		}

		public function payment_fields() {

			global $woocommerce;

			// display gateway description
			echo esc_html($this->get_option( 'description' )); echo wp_kses('<br/><br/>', array('br'=>array()));

			woocommerce_form_field( 'zaad_number', array(
				'type'            => 'text',
				'placeholder'     => 'Enter your EVC or ZAAD number',
				'required'        => true,
				'class'           => array('zaad_field', 'form-row-wide'),
			), WC()->checkout->get_value( 'zaad_number' ) );
	   }

		public	function process_payment($order_id)
		{
			global $woocommerce;

			if( isset( $_POST['zaad_number']) && !empty( $_POST['zaad_number'] ) )
			{
				$account= sanitize_text_field($_POST['zaad_number']);
				$gateway = "zaad";
			}

			$order = new WC_Order($order_id);
			$orderarray = json_decode($order, true);	
			$billing_address = $orderarray['billing'];
			$currency = $order->get_currency();
			$total_order_amount = $order->order_total;

			// Sifalo Pay payment processing
			$response = $this->process_sifalo_payment($total_order_amount, $order_id, $billing_address, $currency, $account, $gateway);

			if ($response == "success") {
				// Mark as on-hold 
				$order->update_status('processing', __('Payment Recieved', 'woocommerce'));

				// Remove cart
				$woocommerce->cart->empty_cart();

				// Return thankyou redirect
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order)
				);
			}
		}


		public	function process_sifalo_payment($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway)
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class_sifalo_pay_core.php';

			$spay = new sifaloPay(sanitize_text_field($_SESSION['api_user_id']), sanitize_text_field($_SESSION['api_key']));

			$res = $spay->sifaloPay_purchase($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway);

			$this->console_log($total_order_amount);
			if ($res['code'] == '601') {
				//success response
				return "success";
			} else {
				wc_add_notice(  $res['response'], 'error' );
				return "fail";
			}
		}

		public function console_log($output, $with_script_tags = false)
		{
			$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
			if ($with_script_tags) {
				$js_code = '<script>' . $js_code . '</script>';
			}
			echo esc_html($js_code);
		}
	}

	class WC_Gateway_Edahab_Pay extends WC_Payment_Gateway
	{
		public function __construct()
		{

			$this->id                 = 'edahab_pay';

			$this->has_fields         = false;

			$this->method_title       = __('Sifalo Pay - eDahab', 'woocommerce');

			$this->method_description = __('Enable customers to transfer funds via eWallet Systems ( Edahab ).');
			// load the settings
			$this->init_form_fields();
			$this->init_settings();
			// Define variables set by the user in the admin section

			$this->title            = $this->get_option('title');

			$this->description      = $this->get_option('description');

			$this->instructions     = $this->get_option('instructions', $this->description);

			$_SESSION['api_user_id'] = $this->get_option('api_user_id');
			$_SESSION['api_key'] = $this->get_option('api_key');

			// fill the rest
			if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			} else {
				add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
			}
		}

		public function init_form_fields()
		{

			$this->form_fields = array(

				'enabled' => array(

					'title'   => __('Enable/Disable', 'woocommerce'),

					'type'    => 'checkbox',

					'label'   => __('Enable Sifalo Pay - eDahab', 'woocommerce'),

					'default' => 'yes'

				),

				'title' => array(

					'title'       => __('Title', 'woocommerce'),

					'type'        => 'text',

					'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),

					'default'     => __('Sifalo Pay - eDahab', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,
					
					'custom_attributes' => array(
						'readonly' => 'readonly' // Set the field as readonly
					)
				),

				'description' => array(

					'title'       => __('Description', 'woocommerce'),

					'type'        => 'textarea',

					'default'     => __('Pay via Sifalo Pay - eDahab'),

					'desc_tip'    => true,

				),

				'instructions' => array(

					'title'       => __('Instructions', 'woocommerce'),

					'type'        => 'textarea',

					'description' => __('Instructions that will be added to the thank you page and emails.', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

				),

				'api_user_id' => array(

					'title'       =>  __('API User', 'woocommerce'),

					'type'        => 'text',
					
					'desc_tip'    => true,

				),
				'api_key' => array(

					'title'       =>  __('API Key', 'woocommerce'),

					'type'        => 'password',

					'desc_tip'    => true,

				),

			);
		}
		public function admin_options()
		{

			/*

			     *The heading and paragraph below are the ones that appear on the backend M-PESA settings page

			     */

			echo '<h3>' . 'Sifalo Pay - eDahab' . '</h3>';



			echo '<p>' . 'eWallet Payments Simplified' . '</p>';



			echo '<table class="form-table">';



			$this->generate_settings_html();



			echo '</table>';
		}

		public function payment_fields() {

			global $woocommerce;

			// display gateway description
			echo esc_html($this->get_option( 'description' )); echo wp_kses('<br/><br/>', array('br'=>array()));

			woocommerce_form_field( 'edahab_number', array(
				'type'            => 'text',
				'placeholder'     => 'Enter your eDahab number',
				'required'        => true,
				'class'           => array('edahab_field', 'form-row-wide'),
			), WC()->checkout->get_value( 'edahab_number' ) );
	   }

		public	function process_payment($order_id)
		{
			global $woocommerce;

			if( isset( $_POST['edahab_number']) && !empty( $_POST['edahab_number'] ) )
			{
				$account= sanitize_text_field($_POST['edahab_number']);
				$gateway = "edahab";
			}

			$order = new WC_Order($order_id);
			$orderarray = json_decode($order, true);	
			$billing_address = $orderarray['billing'];
			$currency = $order->get_currency();
			$total_order_amount = $order->order_total;

			// Sifalo Pay payment processing
			$response = $this->process_sifalo_payment($total_order_amount, $order_id, $billing_address, $currency, $account, $gateway);

			if ($response == "success") {
				// Mark as on-hold 
				$order->update_status('processing', __('Payment Recieved', 'woocommerce'));

				// Remove cart
				$woocommerce->cart->empty_cart();

				// Return thankyou redirect
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order)
				);
			}
		}


		public	function process_sifalo_payment($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway)
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class_sifalo_pay_core.php';

			$spay = new sifaloPay(sanitize_text_field($_SESSION['api_user_id']), sanitize_text_field($_SESSION['api_key']));

			$res = $spay->sifaloPay_purchase($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway);

			$this->console_log($total_order_amount);
			if ($res['code'] == '601') {
				//success response
				return "success";
			} else {
				wc_add_notice(  $res['response'], 'error' );
				return "fail";
			}
		}

		public function console_log($output, $with_script_tags = false)
		{
			$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
			if ($with_script_tags) {
				$js_code = '<script>' . $js_code . '</script>';
			}
			echo esc_html($js_code);
		}
	}

	class WC_Gateway_Premier_Wallet_Pay extends WC_Payment_Gateway
	{
		public function __construct()
		{

			$this->id                 = 'premier_wallet_pay';

			$this->has_fields         = false;

			$this->method_title       = __('Sifalo Pay - Premier Wallet', 'woocommerce');

			$this->method_description = __('Enable customers to transfer funds via eWallet Systems ( Premier Wallet ).');
			// load the settings
			$this->init_form_fields();
			$this->init_settings();
			// Define variables set by the user in the admin section

			$this->title            = $this->get_option('title');

			$this->description      = $this->get_option('description');

			$this->instructions     = $this->get_option('instructions', $this->description);

			$_SESSION['api_user_id'] = $this->get_option('api_user_id');
			$_SESSION['api_key'] = $this->get_option('api_key');

			// fill the rest
			if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			} else {
				add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
			}
		}

		public function init_form_fields()
		{

			$this->form_fields = array(

				'enabled' => array(

					'title'   => __('Enable/Disable', 'woocommerce'),

					'type'    => 'checkbox',

					'label'   => __('Enable Sifalo Pay - Premier Wallet', 'woocommerce'),

					'default' => 'yes'

				),

				'title' => array(

					'title'       => __('Title', 'woocommerce'),

					'type'        => 'text',

					'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),

					'default'     => __('Sifalo Pay - Premier Wallet', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

					'custom_attributes' => array(
						'readonly' => 'readonly' // Set the field as readonly
					)
				),

				'description' => array(

					'title'       => __('Description', 'woocommerce'),

					'type'        => 'textarea',

					'default'     => __('Pay via Sifalo Pay - Premier Wallet'),

					'desc_tip'    => true,

				),

				'instructions' => array(

					'title'       => __('Instructions', 'woocommerce'),

					'type'        => 'textarea',

					'description' => __('Instructions that will be added to the thank you page and emails.', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

				),

				'api_user_id' => array(

					'title'       =>  __('API User', 'woocommerce'),

					'type'        => 'text',
					
					'desc_tip'    => true,

				),
				'api_key' => array(

					'title'       =>  __('API Key', 'woocommerce'),

					'type'        => 'password',

					'desc_tip'    => true,

				),

			);
		}
		public function admin_options()
		{

			/*

			     *The heading and paragraph below are the ones that appear on the backend M-PESA settings page

			     */

			echo '<h3>' . 'Sifalo Pay - Premier Wallet' . '</h3>';



			echo '<p>' . 'eWallet Payments Simplified' . '</p>';



			echo '<table class="form-table">';



			$this->generate_settings_html();



			echo '</table>';
		}

		public function payment_fields() {

			global $woocommerce;

			// display gateway description
			echo esc_html($this->get_option( 'description' )); echo wp_kses('<br/><br/>', array('br'=>array()));

			woocommerce_form_field( 'pbwallet_number', array(
				'type'            => 'text',
				'placeholder'     => 'Enter your wallet account',
				'required'        => true,
				'class'           => array('pbwallet_field', 'form-row-wide'),
			), WC()->checkout->get_value( 'pbwallet_number' ) );
	   }

		public	function process_payment($order_id)
		{
			global $woocommerce;

			if( isset( $_POST['pbwallet_number']) && !empty( $_POST['pbwallet_number'] ) )
			{
				$account= sanitize_text_field($_POST['pbwallet_number']);
				$gateway = "pbwallet";
			}

			$order = new WC_Order($order_id);
			$orderarray = json_decode($order, true);	
			$billing_address = $orderarray['billing'];
			$currency = $order->get_currency();
			$total_order_amount = $order->order_total;

			// Sifalo Pay payment processing
			$response = $this->process_sifalo_payment($total_order_amount, $order_id, $billing_address, $currency, $account, $gateway);

			if ($response == "success") {
				// Mark as on-hold 
				$order->update_status('processing', __('Payment Recieved', 'woocommerce'));

				// Remove cart
				$woocommerce->cart->empty_cart();

				// Return thankyou redirect
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order)
				);
			}
		}


		public	function process_sifalo_payment($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway)
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class_sifalo_pay_core.php';

			$spay = new sifaloPay(sanitize_text_field($_SESSION['api_user_id']), sanitize_text_field($_SESSION['api_key']));

			$res = $spay->sifaloPay_purchase($total_order_amount, $order_ref, $billing_address, $currency, $account, $gateway);

			$this->console_log($total_order_amount);
			if ($res['code'] == '601') {
				//success response
				return "success";
			} else {
				wc_add_notice(  $res['response'], 'error' );
				return "fail";
			}
		}

		public function console_log($output, $with_script_tags = false)
		{
			$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
			if ($with_script_tags) {
				$js_code = '<script>' . $js_code . '</script>';
			}
			echo esc_html($js_code);
		}
	}

	class WC_Gateway_Card_Pay extends WC_Payment_Gateway
	{
		public function __construct()
		{

			$this->id                 = 'card_pay';

			$this->has_fields         = false;

			$this->method_title       = __('Sifalo Pay - Mastercard, Visa, American Express', 'woocommerce');

			$this->method_description = __('Enable customers to transfer funds via Card Payments ( Mastercard, Visa, American Express ).');
			// load the settings
			$this->init_form_fields();
			$this->init_settings();
			// Define variables set by the user in the admin section

			$this->title            = $this->get_option('title');

			$this->description      = $this->get_option('description');

			$this->instructions     = $this->get_option('instructions', $this->description);

			$_SESSION['api_user_id'] = $this->get_option('api_user_id');
			$_SESSION['api_key'] = $this->get_option('api_key');

			// fill the rest
			if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			} else {
				add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
			}
		}

		public function init_form_fields()
		{

			$this->form_fields = array(

				'enabled' => array(

					'title'   => __('Enable/Disable', 'woocommerce'),

					'type'    => 'checkbox',

					'label'   => __('Enable Sifalo Pay - Mastercard, Visa, American Express', 'woocommerce'),

					'default' => 'yes'

				),

				'title' => array(

					'title'       => __('Title', 'woocommerce'),

					'type'        => 'text',

					'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),

					'default'     => __('Sifalo Pay - Mastercard, Visa, American Express', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

					'custom_attributes' => array(
						'readonly' => 'readonly' // Set the field as readonly
					)

				),

				'description' => array(

					'title'       => __('Description', 'woocommerce'),

					'type'        => 'textarea',

					'default'     => __('Pay via Sifalo Pay - Mastercard, Visa, American Express'),

					'desc_tip'    => true,

				),

				'instructions' => array(

					'title'       => __('Instructions', 'woocommerce'),

					'type'        => 'textarea',

					'description' => __('Instructions that will be added to the thank you page and emails.', 'woocommerce'),

					'css'         => 'textarea { read-only};',

					'desc_tip'    => true,

				),

				'api_environment' => array(
					'title'   => __('Payment Mode', 'woocommerce'),
					'type'    => 'select',
					'options' => array(
						'https://phpstack-889786-3206524.cloudwaysapps.com/gateway/' 	=> __('Test', 'woocommerce'),
						'https://api.sifalopay.com/gateway/'    						=> __('Live', 'woocommerce')
					),
					'default' => 'test',
					'desc_tip' => true,
					'description' => __('Choose the Payment Mode for Sifalo Pay.', 'woocommerce')
				),

				'api_user_id' => array(

					'title'       =>  __('API User', 'woocommerce'),

					'type'        => 'text',
					
					'desc_tip'    => true,

				),
				'api_key' => array(

					'title'       =>  __('API Key', 'woocommerce'),

					'type'        => 'password',

					'desc_tip'    => true,

				),

			);
		}

		public function admin_options()
		{

			/*

			     *The heading and paragraph below are the ones that appear on the backend M-PESA settings page

			     */

			echo '<h3>' . 'Sifalo Pay - Mastercard, Visa, American Express' . '</h3>';



			echo '<p>' . 'eWallet Payments Simplified' . '</p>';



			echo '<table class="form-table">';



			$this->generate_settings_html();



			echo '</table>';
		}

		public function payment_fields() {

			global $woocommerce;

			echo esc_html($this->get_option( 'description' )); echo wp_kses('<br/><br/>', array('br'=>array()));
	   }

		public	function process_payment($order_id)
		{
			global $woocommerce;

			$order = new WC_Order($order_id);
			$orderarray = json_decode($order, true);	
			$billing_address = $orderarray['billing'];
			$currency = $order->get_currency();
			$total_order_amount = $order->order_total;
			$thanku_url = $this->get_return_url($order);
			$api_environment = $this->get_option('api_environment');

			// Sifalo Pay payment processing
			$response = $this->process_sifalo_payment($total_order_amount, $order_id, $thanku_url, $api_environment, $billing_address, $currency);

			if( $api_environment == 'https://phpstack-889786-3206524.cloudwaysapps.com/gateway/' )
			{
				$redirect_url = 'https://pay.sifalo.net/checkout/?key=' . $response['key'] . '&token=' . $response['token'];
			}
			else
			{
				$redirect_url = 'https://pay.sifalo.com/checkout/?key=' . $response['key'] . '&token=' . $response['token'];
			}

			return array(

			'result' => 'success',

			'redirect' => $redirect_url
			); 
		}


		public	function process_sifalo_payment($total_order_amount, $order_ref, $thanku_url, $api_environment, $billing_address, $currency)
		{
			require_once plugin_dir_path(__FILE__) . 'includes/class_sifalo_pay_core.php';

			$spay = new sifaloPay(sanitize_text_field($_SESSION['api_user_id']), sanitize_text_field($_SESSION['api_key']));

			$res = $spay->sifaloPay_purchase2($total_order_amount, $order_ref, $thanku_url, $api_environment, $billing_address, $currency);

			$this->console_log($total_order_amount);
			if ( isset( $res['key'] ) && isset( $res['token'] ) ) 
			{
				return $res;
			} 
			else 
			{
				wc_add_notice(  $res['response'], 'error' );
				return "fail";
			}
		}

		public function console_log($output, $with_script_tags = false)
		{
			$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
			if ($with_script_tags) {
				$js_code = '<script>' . $js_code . '</script>';
			}
			echo esc_html($js_code);
		}
	}
}

function add_sifalo_pay_class($methods)
{

	// $methods[] = 'WC_Gateway_Sifalo_Pay';
	$methods[] = 'WC_Gateway_ZES_Pay';
	$methods[] = 'WC_Gateway_Edahab_Pay';
	$methods[] = 'WC_Gateway_Premier_Wallet_Pay';
	$methods[] = 'WC_Gateway_Card_Pay';

	return $methods;
}
add_filter('woocommerce_payment_gateways', 'add_sifalo_pay_class');

function iscu_wp_head(){

	if ( is_checkout() ) 
	{	
		?>
			<style>
				#payment .payment_methods > .wc_payment_method > label {
					display: flex;
				}
				.iscu_sifalo_pay {
					float: unset !important;
					width: 150px;
				}
			</style>
		<?php
	}
}

add_action( 'wp_head', 'iscu_wp_head' );

function iscu_admin_head(){

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'checkout' && ( ( isset( $_GET['section'] ) && $_GET['section'] == 'card_pay' )  ) )
	{	
		?>
			<style>
				.form-table tr:nth-child(6),
				.form-table tr:nth-child(7) {
					display: none;
				}
			</style>
		<?php
	}
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'checkout' && ( ( isset( $_GET['section'] ) && $_GET['section'] == 'zes_pay' ) || ( isset( $_GET['section'] ) && $_GET['section'] == 'edahab_pay' ) || ( isset( $_GET['section'] ) && $_GET['section'] == 'premier_wallet_pay' ) ) ) 
	{	
		?>
			<style>
				.form-table tr:nth-child(5),
				.form-table tr:nth-child(6) {
					display: none;
				}
			</style>
		<?php
	}

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] == 'checkout' ) 
	{
		?>
			<style>
				.wc-payment-gateway-method-name {
					display: none !important;
				}
			</style>
		<?php
	}
}

add_action( 'admin_head', 'iscu_admin_head' );

function iscu_custom_settings_tab( $settings_tabs ) {

	$settings_tabs['sifalo_api'] = __( 'Sifalo Pay', 'woocommerce' );

	return $settings_tabs;
}

add_filter( 'woocommerce_settings_tabs_array', 'iscu_custom_settings_tab', 50 );

function sifalo_api_tab_content() {

    woocommerce_admin_fields(
        array(
            'section_title' => array(
                'name' => __( 'Sifalo Pay  Api Credentials', 'woocommerce' ),
                'type' => 'title',
				'desc' => '<p>You can get this from sifalo pay portal.</p>',
                'id'   => 'sifalo_pay_title'
            ),
            'sifalo_pay_api_user' => array(
                'name'     => __( 'Sifalo Pay API User', 'woocommerce' ),
                'type'     => 'text',
                'id'       => 'sifalo_pay_api_user',
				'default'  => !empty( get_option( 'sifalo_pay_api_user' ) ) ? get_option( 'sifalo_pay_api_user' ) : '',
            ),
            'sifalo_pay_api_key' => array(
                'name'     => __( 'Sifalo Pay API Key', 'woocommerce' ),
                'type'     => 'password',
                'id'       => 'sifalo_pay_api_key',
				'default'  => !empty( get_option( 'sifalo_pay_api_key' ) ) ? get_option( 'sifalo_pay_api_key' ) : '',
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id'   => 'custom_section_end'
            )
        )
    );
}

add_action( 'woocommerce_settings_tabs_sifalo_api', 'sifalo_api_tab_content' );

function sifalo_save_custom_settings() {

	update_option( 'sifalo_pay_api_user', $_POST['sifalo_pay_api_user'] );
	update_option( 'sifalo_pay_api_key', $_POST['sifalo_pay_api_key'] );

	$iscu_zes_pay_settings = get_option( 'woocommerce_zes_pay_settings' );

	$iscu_zes_pay_settings['api_user_id'] = $_POST['sifalo_pay_api_user'];
	$iscu_zes_pay_settings['api_key'] = $_POST['sifalo_pay_api_key'];

	update_option( 'woocommerce_zes_pay_settings', $iscu_zes_pay_settings );

	$iscu_edahab_pay_settings = get_option( 'woocommerce_edahab_pay_settings' );

	$iscu_edahab_pay_settings['api_user_id'] = $_POST['sifalo_pay_api_user'];
	$iscu_edahab_pay_settings['api_key'] = $_POST['sifalo_pay_api_key'];

	update_option( 'woocommerce_edahab_pay_settings', $iscu_edahab_pay_settings );

	$iscu_premier_wallet_pay_settings = get_option( 'woocommerce_premier_wallet_pay_settings' );

	$iscu_premier_wallet_pay_settings['api_user_id'] = $_POST['sifalo_pay_api_user'];
	$iscu_premier_wallet_pay_settings['api_key'] = $_POST['sifalo_pay_api_key'];

	update_option( 'woocommerce_premier_wallet_pay_settings', $iscu_premier_wallet_pay_settings );

	$iscu_card_pay_settings = get_option( 'woocommerce_card_pay_settings' );

	$iscu_card_pay_settings['api_user_id'] = $_POST['sifalo_pay_api_user'];
	$iscu_card_pay_settings['api_key'] = $_POST['sifalo_pay_api_key'];

	update_option( 'woocommerce_card_pay_settings', $iscu_card_pay_settings );
}

add_action( 'woocommerce_update_options_sifalo_api', 'sifalo_save_custom_settings' );

function custom_gateway_title_text( $title, $payment_gateway ) {
/*
	if ( is_admin() ) 
	{
		return $title;
	}
	else
	{
		if ( $payment_gateway == 'zes_pay' || $payment_gateway == 'edahab_pay' || $payment_gateway == 'premier_wallet_pay' || $payment_gateway == 'card_pay' ) 
		{
			$title = str_replace( 'Sifalo Pay', '<img src="https://pay.sifalo.com/includes/assets/images/logo/logo-dark.png" alt="Sifalo Pay" class="iscu_sifalo_pay"> ', $title );
		}
	}
*/
    return $title;
}

add_filter( 'woocommerce_gateway_title', 'custom_gateway_title_text', 10, 2 );

function iscu_payment_verification(){

	global $woocommerce;

	if( isset( $_GET['sid'] ) )
	{
		$order_id = '';
		if( is_wc_endpoint_url( 'order-received' ) ) 
		{
			$order_id 	= get_query_var('order-received');
			$order 		= wc_get_order($order_id);
		 }

		if ( !empty( $order_id ) ) 
		{
			update_post_meta( $order_id, 'iscu_sifalo_sid', $_GET['sid'] );

			$verify_url ='https://api.sifalopay.com/gateway/verify.php';
			$args 		= array(
				'sid'        => $_GET['sid'],
				);

			$response = wp_remote_post( $verify_url, $args );
		
			if ( wp_remote_retrieve_response_code( $response ) == '200' ) 
			{
				$order->update_status( 'processing', __( 'Payment Recieved', 'woocommerce' ) );
				$woocommerce->cart->empty_cart();
				if( !isset( $_GET['key'] ) )
				{
					$thanku_url = $order->get_checkout_order_received_url().'&sid='.$_GET['sid'];
					wp_redirect($thanku_url);
				}
			}
		}
		
	}

}

add_action( 'wp' , 'iscu_payment_verification' );

function iscu_edit_order_meta( $order_id ){
	
	$order = wc_get_order( $order_id );
	$payment_method_title = $order->get_payment_method_title();
	$payment_method_title_without_image = preg_replace('/<img[^>]+>/', 'Sifalo Pay', $payment_method_title);
	$order->set_payment_method_title( $payment_method_title_without_image );
	$order->save();

}

add_action( 'woocommerce_thankyou' , 'iscu_edit_order_meta' , 99 );

function run_default_settings_on_update() {
    
	$sifalo_pay_api_user 	= get_option('sifalo_pay_api_user');
	$sifalo_pay_api_key 	= get_option('sifalo_pay_api_key');
	$iscu_card_pay_settings	= get_option( 'woocommerce_card_pay_settings' );
	
	if( ( empty( $sifalo_pay_api_user ) && empty( $sifalo_pay_api_key ) ) || empty( $iscu_card_pay_settings ) )
	{
		iscu_default_settings();
	}
}

add_action( 'admin_init', 'run_default_settings_on_update' );