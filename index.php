<?php
/**
 * Plugin Name: Booknetic Extras
 * Description: Replaces Google Maps with OpenStreetMap in Booknetic (Unofficial Addon)
 * Version: 1.0.1
 * Author: DanSP 
 * Author URI: https://github.com/dansp89/booknetic-extras
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Requires PHP: 8.2
 * Requires WP: 6.2
 * Text Domain: booknetic-extras
 * 
 * This is an UNOFFICIAL addon for Booknetic.
 * Booknetic is a commercial plugin available at https://www.booknetic.com/
 * This addon is not affiliated with or endorsed by FS-Code.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('BOOKNETIC_EXTRAS_VERSION', '1.0.1');
define('BOOKNETIC_EXTRAS_DIR', plugin_dir_path(__FILE__));
define('BOOKNETIC_EXTRAS_URL', plugin_dir_url(__FILE__));

register_activation_hook(__FILE__, 'booknetic_extras_activate');
register_deactivation_hook(__FILE__, 'booknetic_extras_deactivate');

require_once __DIR__ . '/loader.php';

