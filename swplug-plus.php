<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://medium.com/swplug
 * @since             1.0.0
 * @package           SWPLUG
 *
 * @wordpress-plugin
 * Plugin Name:       SWPLUG Plus
 * Plugin URI:        https://swplug.com
 * Description:       Leading Wordpress Payment Plugin for Stellar Blockchain. 
 * Version:           1.1.5
 * Author:            Swplug plus
 * Author URI:        https://medium.com/swplug
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       swplug
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'SWPLUG_Plus_VERSION', '1.0.0' );

add_filter('plugin_row_meta',  'Register_Plugins_Links', 10, 2);
function Register_Plugins_Links ($links, $file) {
               $base = plugin_basename(__FILE__);
               if ($file == $base) {
                       $links[] = '<a href="https://swplug.com/logo.png" target="_blank">' . __('Swplug Logo') . '</a>';
               }
               return $links;
       }

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-stellar-activator.php
 */
function activate_stellar() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stellar-activator.php';
	Stellar_Activator::activate();
}



/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-stellar-deactivator.php
 */
function deactivate_stellar() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-stellar-deactivator.php';
	Stellar_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_stellar' );
register_deactivation_hook( __FILE__, 'deactivate_stellar' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-stellar.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_stellar() {

	$plugin = new Stellar();
	$plugin->run();

}
run_stellar();
