<?php
/**
 * Group list for the Visual Builder condition picker.
 *
 * @package GroupsDiviConditions
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST route that lists Groups for editors.
 */
class Groups_Divi_Conditions_REST {

	/**
	 * Register the route.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_route' ) );
	}

	/**
	 * Register GET /groups-divi-conditions/v1/groups.
	 *
	 * @return void
	 */
	public static function register_route(): void {
		register_rest_route(
			'groups-divi-conditions/v1',
			'/groups',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_groups' ),
				'permission_callback' => array( __CLASS__, 'can_list_groups' ),
			)
		);
	}

	/**
	 * Editors who can change Divi layouts may read the group list.
	 *
	 * @return bool
	 */
	public static function can_list_groups(): bool {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Return group IDs and names.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public static function list_groups() {
		if ( ! Groups_Divi_Conditions_Evaluator::groups_available() ) {
			return new WP_Error(
				'groups_divi_conditions_groups_inactive',
				__( 'Groups is not active.', 'groups-divi-conditions' ),
				array( 'status' => 503 )
			);
		}

		$rows  = Groups_Group::get_groups(
			array(
				'order_by' => 'name',
				'order'    => 'ASC',
			)
		);
		$items = array();

		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				if ( ! is_object( $row ) || ! isset( $row->group_id, $row->name ) ) {
					continue;
				}

				$items[] = array(
					'id'   => (int) $row->group_id,
					'name' => (string) $row->name,
				);
			}
		}

		return rest_ensure_response(
			array(
				'groups' => $items,
			)
		);
	}
}
