<?php
// use \ZuluCrypto\MobiusApi\Mobius;
// use \ZuluCrypto\MobiusApi\Exception\MobiusApiException;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SWPlug
 * @subpackage SWPlug/admin
 * @author     Ali <manknojiya121@gmail.com>
 */
class SWPlug_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		/**
		 * Register a custom menu page.
		 */
		include( plugin_dir_path( __FILE__ ) . 'woocommerce-gateway-stellar.php'); 


		/**
		 * For mobius plugin
		 */
		//include(plugin_dir_path( __FILE__ ) . 'woocommerce-gateway-mobius.php');

		// require_once( plugin_dir_path( __FILE__ ) . 'mobius/vendor/autoload.php');



		function get_mobius_front($atts) {
			$atts = shortcode_atts(
				array(
					'charge' => '',
				), $atts, 'get_mobius_front' );

			$charge_val = $atts['charge'];
			include( plugin_dir_path( __FILE__ ) . 'mobius_front.php');                 
		}
		add_shortcode('mobius_front', 'get_mobius_front');


	}          

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Stellar_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Stellar_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/stellar-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Stellar_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Stellar_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/stellar-admin.js?version=1.2.3.4', array( 'jquery' ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'myplugin' , array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );




	}

	// deprecated
	function swplug_callCurl($url,$params,$method)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$body = $params;
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); 
		curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	} 

	public function swplug_get_balance() {

		check_ajax_referer( 'get_balance_secure', 'security' );

		$url = "https://mobius.network/api/v1/app_store/balance?";
		$params = array(
			"api_key" => sanitize_text_field($_REQUEST['api_key']),
			"app_uid" => sanitize_text_field($_REQUEST['app_uid']),
			"email" => sanitize_email($_REQUEST['email']),
		);
		$query_string = http_build_query($params);

		$response = wp_remote_get(sprintf("%s%s", $url, $query_string));
		if (!is_array($response)) {
			echo wp_remote_retrieve_body($response);
		}

		exit;

	}

	public function swplug_delete_last_mobius_order() {
		check_ajax_referer( 'get_balance_secure', 'security' );
		global $wpdb;
		$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_type = 'shop_order' ORDER BY id DESC LIMIT 1";
		$result = $wpdb->get_results($sql);
		$order_id = $result[0]->ID;
		echo $sql = "DELETE FROM ".$wpdb->prefix."posts WHERE ID=".$order_id;
		$wpdb->query($sql);
	}

	public function swplug_use_credits() {
		check_ajax_referer( 'get_balance_secure', 'security' );
		$url = "https://mobius.network/api/v1/app_store/use?";
		$params = array(
			"api_key" => sanitize_text_field($_REQUEST['api_key']),
			"app_uid" => sanitize_text_field($_REQUEST['app_uid']),
			"email" => sanitize_email($_REQUEST['email']),
			"num_credits" => sanitize_text_field($_REQUEST['num_credits']),
		);
		$response = wp_remote_post($url, [ 'body' => $params ]);
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		}
		echo $response;
	}

}


