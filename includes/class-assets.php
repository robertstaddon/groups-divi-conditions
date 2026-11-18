<?php
/**
 * Visual Builder assets.
 *
 * @package GroupsDiviConditions
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the condition UI inside the Divi 5 Visual Builder app window.
 */
class Groups_Divi_Conditions_Assets {

	/**
	 * Register enqueue hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Enqueue the condition script only in the Visual Builder app window.
	 *
	 * @return void
	 */
	public static function enqueue(): void {
		if ( ! Groups_Divi_Conditions_Evaluator::groups_available() ) {
			return;
		}

		if ( ! class_exists( '\ET\Builder\Framework\Utility\Conditions' ) ) {
			return;
		}

		if ( ! \ET\Builder\Framework\Utility\Conditions::is_vb_app_window() ) {
			return;
		}

		wp_enqueue_script(
			'groups-divi-conditions-vb',
			plugins_url( 'js/vb-groups-condition.js', GROUPS_DIVI_CONDITIONS_FILE ),
			array( 'divi-vendor-wp-hooks', 'divi-vendor-react', 'wp-api-fetch' ),
			GROUPS_DIVI_CONDITIONS_VERSION,
			true
		);

		wp_localize_script(
			'groups-divi-conditions-vb',
			'groupsDiviConditions',
			array(
				'restPath' => 'groups-divi-conditions/v1/groups',
				'i18n'     => array(
					'label'            => __( 'Groups Membership', 'groups-divi-conditions' ),
					'displayRule'      => __( 'Display rule', 'groups-divi-conditions' ),
					'isMember'         => __( 'Is a member of', 'groups-divi-conditions' ),
					'isNotMember'      => __( 'Is not a member of', 'groups-divi-conditions' ),
					'groups'           => __( 'Groups', 'groups-divi-conditions' ),
					'anyOf'            => __( 'The visitor matches if they belong to any selected group.', 'groups-divi-conditions' ),
					'loading'          => __( 'Loading groups…', 'groups-divi-conditions' ),
					'none'             => __( 'No groups found.', 'groups-divi-conditions' ),
					'loadError'        => __( 'Groups could not be loaded.', 'groups-divi-conditions' ),
					'missing'          => __( 'This group no longer exists.', 'groups-divi-conditions' ),
					'tooltipIs'        => __( 'Display when the visitor is a member of %s.', 'groups-divi-conditions' ),
					'tooltipIsNot'     => __( 'Display when the visitor is not a member of %s.', 'groups-divi-conditions' ),
					'tooltipEmpty'     => __( 'No groups selected.', 'groups-divi-conditions' ),
				),
			)
		);
	}
}
