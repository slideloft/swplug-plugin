<?php
/**
 * Fired during plugin activation
 *
 * @link       https://medium.com/swplug
 * @since      1.0.0
 *
 * @package    Stellar
 * @subpackage Stellar/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    SWPLUG
 * @subpackage SWPLUG/includes
 * @author     SWPLUG PLUS <medium.com/swplug>
 */
class Stellar_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
         global $wp;

         update_option('woocommerce_stellar_gateway_settings', '');
    }

}