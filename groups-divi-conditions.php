<?php
/**
 * Plugin Name: Groups for Divi Conditions
 * Description: Adds a Groups Membership display condition to Divi 5.
 * Version: 1.0.1
 * Author: Abundant Designs
 * Author URI: https://www.abundantdesigns.com
 * Update URI: https://manage.abundantdesigns.com/wp-json/update-server/groups-divi-conditions/
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Text Domain: groups-divi-conditions
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package GroupsDiviConditions
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GROUPS_DIVI_CONDITIONS_VERSION', '1.0.1' );
define( 'GROUPS_DIVI_CONDITIONS_FILE', __FILE__ );
define( 'GROUPS_DIVI_CONDITIONS_DIR', plugin_dir_path( __FILE__ ) );

require_once GROUPS_DIVI_CONDITIONS_DIR . 'includes/class-evaluator.php';
require_once GROUPS_DIVI_CONDITIONS_DIR . 'includes/class-frontend.php';
require_once GROUPS_DIVI_CONDITIONS_DIR . 'includes/class-rest.php';
require_once GROUPS_DIVI_CONDITIONS_DIR . 'includes/class-assets.php';
require_once GROUPS_DIVI_CONDITIONS_DIR . 'includes/class-plugin.php';

add_action( 'plugins_loaded', array( 'Groups_Divi_Conditions_Plugin', 'boot' ) );
