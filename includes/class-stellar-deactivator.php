<?php
/**
 * Fired during plugin deactivation
 *
 * @link       http://www.stellar.com
 * @since      1.0.0
 *
 * @package    swplug-plus
 * @subpackage swplug-plus/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    swplug-plus
 * @subpackage swplug-plus/includes
 * @author     SWPLUG PLUS <medium.com/swplug>
 */
class Stellar_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
                
            delete_option( 'woocommerce_stellar_gateway_settings' );
            delete_option( 'woocommerce_mobius_gateway_settings' );
            
	}

}
