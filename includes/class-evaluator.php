<?php
/**
 * Groups membership checks for a Divi condition.
 *
 * @package GroupsDiviConditions
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Evaluates groupsMembership condition settings.
 */
class Groups_Divi_Conditions_Evaluator {

	/**
	 * Memo of user/group membership for this request.
	 *
	 * @var array<string, bool>
	 */
	private static $membership = array();

	/**
	 * Whether the visitor matches the condition settings.
	 *
	 * Empty selections and a missing Groups plugin follow Divi's User Role rule:
	 * "is" hides the module and "is not" shows it.
	 *
	 * @param array<string, mixed> $settings Condition settings.
	 * @return bool
	 */
	public static function matches( array $settings ): bool {
		$rule   = isset( $settings['displayRule'] ) && 'isNot' === $settings['displayRule'] ? 'isNot' : 'is';
		$groups = self::selected_group_ids( $settings );

		if ( array() === $groups || ! self::groups_available() ) {
			return 'isNot' === $rule;
		}

		$user_id = get_current_user_id();
		$member  = false;

		foreach ( $groups as $group_id ) {
			if ( ! self::group_exists( $group_id ) ) {
				continue;
			}

			if ( self::user_is_member( $user_id, $group_id ) ) {
				$member = true;
				break;
			}
		}

		return 'is' === $rule ? $member : ! $member;
	}

	/**
	 * Selected group IDs, ignoring anything that is not a positive ID.
	 *
	 * @param array<string, mixed> $settings Condition settings.
	 * @return array<int, int>
	 */
	private static function selected_group_ids( array $settings ): array {
		$raw = isset( $settings['groups'] ) && is_array( $settings['groups'] ) ? $settings['groups'] : array();
		$ids = array();

		foreach ( $raw as $group ) {
			if ( is_object( $group ) ) {
				$group = get_object_vars( $group );
			}

			if ( ! is_array( $group ) || ! isset( $group['value'] ) || ! is_numeric( $group['value'] ) ) {
				continue;
			}

			$group_id = (int) $group['value'];

			if ( $group_id > 0 ) {
				$ids[] = $group_id;
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Whether the Groups API this condition calls is loaded.
	 *
	 * @return bool
	 */
	public static function groups_available(): bool {
		return class_exists( 'Groups_Group' ) && class_exists( 'Groups_User' );
	}

	/**
	 * Whether a group ID still exists.
	 *
	 * @param int $group_id Group ID.
	 * @return bool
	 */
	private static function group_exists( int $group_id ): bool {
		$group = Groups_Group::read( $group_id );

		return is_object( $group ) && isset( $group->group_id );
	}

	/**
	 * Direct membership, memoized for the request.
	 *
	 * @param int $user_id User ID. Guests are 0.
	 * @param int $group_id Group ID.
	 * @return bool
	 */
	private static function user_is_member( int $user_id, int $group_id ): bool {
		$key = $user_id . ':' . $group_id;

		if ( ! array_key_exists( $key, self::$membership ) ) {
			self::$membership[ $key ] = (bool) Groups_User::user_is_member( $user_id, $group_id );
		}

		return self::$membership[ $key ];
	}
}
