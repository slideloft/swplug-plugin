<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://medium.com/swplug
 * @since      1.0.0
 *
 * @package    SWPLUG
 * @subpackage SWPLUG/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    SWPLUG
 * @subpackage SWPLUG/public
 * @author     Ali <manknojiya121@gmail.com>
 */
class SWPlug_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
                
                /**
				* Generate shortcode for front
				*/
                function get_stellar_front($atts) {
                    include( plugin_dir_path( __FILE__ ) . 'partials/stellar-public-display.php'); 
                }
               add_shortcode('stellar_front', 'get_stellar_front');

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SWPlug_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SWPlug_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/stellar-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SWPlug_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SWPlug_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/stellar-public.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jquery.qrcode.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/qrcode.js', array( 'jquery' ), $this->version, false );
		
		wp_localize_script( $this->plugin_name, 'myplugin' , array( 'ajax_url' => admin_url('admin-ajax.php', 'https') ) );

	}

	public function swplug_send_order_notification() {

		check_ajax_referer( 'get_balance_secure', 'security' );

// 		$multiple_recipients = array(sanitize_email($_REQUEST['user_email']),sanitize_email($_REQUEST['owner_email']));

        $multiple_recipients = sanitize_email($_REQUEST['owner_email']);
		$subject = 'Swplug Order Details';

		$message = '<html><body>';
		$message .= 'SWPLUG';
		$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
		
		foreach ($_POST as $key => $value) {
			if(isset($value)) {
				if($value != 'swplug_send_order_notification' && $key != 'security') {
					$message .= "<tr><td><strong>".ucfirst(str_replace("_", " ", $key)).":</strong> </td><td>" . strip_tags($value) . "</td></tr>";		
				}
				
			}	
		}
		$message .='</table><br><br>Thanks,<br>Swplug Team.';
		
		// Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        

		wp_mail( $multiple_recipients, $subject, $message, $headers );
		
		if(!empty($_REQUEST['owner_email'])) {
		    $user_email = sanitize_email($_REQUEST['owner_email']);
		
 		    wp_mail( $user_email, $subject, $message, $headers );   
		}
		
		wp_die();
	}

}
