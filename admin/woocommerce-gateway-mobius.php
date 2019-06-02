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
function wc_mobius_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_Mobius';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_mobius_add_to_gateways' );


/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_mobius_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mobius' ) . '">' . __( 'Configure', 'wc-gateway-mobius' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_mobius_gateway_plugin_links' );


/**
 * Offline Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class 		WC_Gateway_Mobius
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		SkyVerge
 */
add_action( 'plugins_loaded', 'wc_mobius_gateway_init', 11 );

function wc_mobius_gateway_init() {

	class WC_Gateway_Mobius extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'mobius_gateway';
			$this->icon               = apply_filters('woocommerce_offline_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'Mobius', 'wc-gateway-mobius' );
			$this->method_description = __( 'Pay with mobius.', 'wc-gateway-mobius' );
		  
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
					'title'   => __( 'Enable/Disable', 'wc-gateway-mobius' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Mobius Payment', 'wc-gateway-mobius' ),
					'default' => 'yes'
				),

				'app_apikey' => array(
					'title'       => __( 'API Key', 'wc-gateway-mobius' ),
					'type'        => 'text',
					'description' => __( 'This controls the apikey for mobius app on DAppstore.', 'wc-gateway-mobius' ),
					'default'     => __( '', 'wc-gateway-mobius' ),
					'desc_tip'    => true,
				),

				'app_uid' => array(
					'title'       => __( 'APP UID', 'wc-gateway-mobius' ),
					'type'        => 'text',
					'description' => __( 'This controls the app UID for mobius app on DAppstore.', 'wc-gateway-mobius' ),
					'default'     => __( '', 'wc-gateway-mobius' ),
					'desc_tip'    => true,
				),

				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-mobius' ),
					'type'        => 'text',
					'description' => __( 'Mobius', 'wc-gateway-mobius' ),
					'default'     => __( 'Mobius', 'wc-gateway-mobius' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'wc-gateway-mobius' ),
					'type'        => 'textarea',
					'description' => __( '[mobius_front charge="100"]', 'wc-gateway-mobius' ),
					'default'     => __( '[mobius_front charge="100"]', 'wc-gateway-mobius' ),
					'desc_tip'    => true,
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

            do_shortcode('[mobius_front charge="100"]');
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
			$order->update_status( 'on-hold', __( 'Awaiting offline payment', 'wc-gateway-mobius' ) );
			
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
	
  } // end \WC_Gateway_Mobius class
}

