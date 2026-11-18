<?php
/**
 * Plugin bootstrap.
 *
 * @package GroupsDiviConditions
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the condition, the REST list, and the Visual Builder script.
 */
class Groups_Divi_Conditions_Plugin {

	/**
	 * Start the plugin after other plugins have loaded.
	 *
	 * @return void
	 */
	public static function boot(): void {
		Groups_Divi_Conditions_Frontend::register();
		Groups_Divi_Conditions_REST::register();
		Groups_Divi_Conditions_Assets::register();

		if ( is_admin() ) {
			add_action( 'admin_notices', array( __CLASS__, 'dependency_notice' ) );
		}
	}

	/**
	 * Tell administrators when Divi or Groups is not active.
	 *
	 * @return void
	 */
	public static function dependency_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$missing = array();

		if ( ! self::divi_available() ) {
			$missing[] = 'Divi';
		}

		if ( ! Groups_Divi_Conditions_Evaluator::groups_available() ) {
			$missing[] = 'Groups';
		}

		if ( array() === $missing ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html(
			sprintf(
				/* translators: %s: comma-separated list of missing plugins. */
				__( 'Groups for Divi Conditions needs %s to be active.', 'groups-divi-conditions' ),
				implode( ', ', $missing )
			)
		);
		echo '</p></div>';
	}

	/**
	 * Whether Divi 5's builder is loaded.
	 *
	 * @return bool
	 */
	private static function divi_available(): bool {
		return class_exists( '\ET\Builder\Framework\Utility\Conditions' );
	}
}
