<?php

defined( 'ABSPATH' ) or exit;


// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}


/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function wc_stellar_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_SWPLUG';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_stellar_add_to_gateways' );


/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_stellar_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=stellar' ) . '">' . __( 'Configure', 'wc-gateway-stellar' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_stellar_gateway_plugin_links' );


/**
 * Offline Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class 		WC_Gateway_Offline
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		SkyVerge
 */
add_action( 'plugins_loaded', 'wc_stellar_gateway_init', 11 );

function wc_stellar_gateway_init() {

	class WC_Gateway_SWPLUG extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'stellar_gateway';
			$this->icon               = apply_filters('woocommerce_offline_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'SWPLUG PLUS', 'wc-gateway-SWplug' );
			$this->method_description = __( 'Pay with SWPLUG PLUS.', 'wc-gateway-stellar' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
		  
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}
	
	
		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'wc_offline_form_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable SWPLUG PLUS Payment', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'wallet_address' => array(
					'title'       => __( 'Wallet Address', 'wc-gateway-stellar' ),
					'type'        => 'text',
					'description' => __( 'This controls the wallet address for stellar app.', 'wc-gateway-stellar' ),
					'default'     => __( '', 'wc-gateway-stellar' ),
					'desc_tip'    => true,
				),

				'memo_timing' => array(
					'title'       => __( 'Memo Time Duration', 'wc-gateway-stellar' ),
					'type'        => 'text',
					'description' => __( 'This controls the memo timing.', 'wc-gateway-stellar' ),
					'default'     => __( '', 'wc-gateway-stellar' ),
					'desc_tip'    => true,
				),

				'owner_email' => array(
					'title'       => __( 'Owner Email', 'wc-gateway-stellar' ),
					'type'        => 'text',
					'description' => __( 'This controls the owner email.', 'wc-gateway-stellar' ),
					'default'     => __( '', 'wc-gateway-stellar' ),
					'desc_tip'    => true,
				),

				'xlm#(stellar$org)' => array(
					'title'   => __( 'XLM (STELLAR.ORG)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable XLM', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'usd#(Anchor#US#Dollar)' => array(
					'title'   => __( 'USD (Anchor US Dollar)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable USD', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'slt#(smartlands)' => array(
					'title'   => __( 'SLT (SMARTLANDS)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable SLT', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'mobi#(mobius#network)' => array(
					'title'   => __( 'MOBI (MOBIUS NETWORK)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable MOBI', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'cny#(ripplefox)' => array(
					'title'   => __( 'CNY (RIPPLEFOX)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable CNY', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'xrp#(vcbear)' => array(
					'title'   => __( 'XRP (VCBEAR)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable XRP', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'eurt#(tempo)' => array(
					'title'   => __( 'EURT (TEMPO)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable EURT', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'pedi#(pedity$com)' => array(
					'title'   => __( 'PEDI (PEDITY.COM)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable PEDI', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'fras#(frasindo$com)' => array(
					'title'   => __( 'FRAS (FRASINDO.COM)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable FRAS', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'azgl#(azgreenlife$com)' => array(
					'title'   => __( 'AZGL (AZGREENLIFE.COM)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable AZGL', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'DICENS#(Dicens$co)' => array(
					'title'   => __( 'DICENS (Dicens.co)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable DICENS', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),

				'ABC#(ABAZAAR$IO)' => array(
					'title'   => __( 'ABC (ABAZAAR.IO)', 'wc-gateway-stellar' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable ABC', 'wc-gateway-stellar' ),
					'default' => 'yes'
				),


				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-stellar' ),
					'type'        => 'text',
					'description' => __( 'SWPLUG-PLUS', 'wc-gateway-stellar' ),
					'default'     => __( 'SWPLUG-PLUS', 'wc-gateway-stellar' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'wc-gateway-stellar' ),
					'type'        => 'textarea',
					'description' => __( 'Pay with SWPLUG-PLUS', 'wc-gateway-stellar' ),
					'default'     => __( 'Pay with SWPLUG-PLUS', 'wc-gateway-stellar' ),
					'desc_tip'    => true,
				),

				'default_currency' => array(
					'title'       => __( 'Default Currency', 'wc-gateway-stellar' ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( 'This currency will be used as default on checkout page', 'wc-gateway-stellar' ),
                    'default'     => 'XLM',
                    'desc_tip'    => true,
                    'options'     => array('XLM' => 'XLM (STELLAR.ORG)', 'USD' => 'USD(STRONGHOLD.CO)','SLT' => 'SLT (SMARTLANDS)', 'MOBI' => 'MOBI (MOBIUS NETWORK)','CNY' => 'CNY (RIPPLEFOX)', 'XRP' => 'XRP (VCBEAR)', 'EURT' => 'EURT (TEMPO)', 'PEDI' => 'PEDI (PEDITY.COM)', 'FRAS' => 'FRAS (FRASINDO.COM)', 'AZGL' => 'AZGL (AZGREENLIFE.COM)', 'DICENS' => 'DICENS (Dicens.co)', 'ABC' => 'ABC (ABAZAAR.IO)'),
				),
				
			) );
		}
	
		
		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}
	
	
		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}
		
		public function payment_fields(){

            if ( $description = $this->get_description() ) {
                // echo wpautop( wptexturize( $description ) );
            }

            echo "<strong><label id='wallet_address_lbl'>".$wallet_address = $this->title = $this->get_option( 'wallet_address' )."</label></strong> <i class='fa fa-copy' id='copy_wallet_adrs'></i>";

            do_shortcode('[stellar_front]');
        }
	
		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
			
			$order = wc_get_order( $order_id );
			
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Awaiting stellar payment', 'wc-gateway-stellar' ) );
			
			// Reduce stock levels
			$order->reduce_order_stock();
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
	
  } // end \WC_Gateway_Offline class
}


