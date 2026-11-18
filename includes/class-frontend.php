<?php
/**
 * Divi 5 condition evaluation.
 *
 * @package GroupsDiviConditions
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles groupsMembership inside Divi's custom-condition filter.
 */
class Groups_Divi_Conditions_Frontend {

	/**
	 * Register the evaluator.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_filter( 'divi_module_options_conditions_is_custom_condition_true', array( __CLASS__, 'evaluate' ), 10, 4 );
	}

	/**
	 * Evaluate a groupsMembership condition. Other condition names pass through.
	 *
	 * @param bool|null            $result   Result from an earlier filter, or null.
	 * @param string               $name     Condition name.
	 * @param array<string, mixed> $settings Condition settings.
	 * @param string               $id       Condition instance ID.
	 * @return bool|null
	 */
	public static function evaluate( $result, $name, $settings, $id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( 'groupsMembership' !== $name ) {
			return $result;
		}

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		return Groups_Divi_Conditions_Evaluator::matches( $settings );
	}
}
