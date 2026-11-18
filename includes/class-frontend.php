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
		add_filter( 'render_block', array( __CLASS__, 'hide_shortcode_module' ), 99, 2 );
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

	/**
	 * Hide a Divi shortcode module whose Groups condition fails.
	 *
	 * divi/shortcode-module is a static block. Divi only discards HTML from modules
	 * that have a render callback, so a condition stored on a shortcode module would
	 * otherwise leave the shortcode in the page. Native Divi modules already do this
	 * themselves and are left alone.
	 *
	 * @param string               $content Rendered block content.
	 * @param array<string, mixed> $block   Parsed block.
	 * @return string
	 */
	public static function hide_shortcode_module( $content, $block ) {
		if ( ! is_array( $block ) || 'divi/shortcode-module' !== ( $block['blockName'] ?? '' ) ) {
			return $content;
		}

		$conditions = $block['attrs']['module']['decoration']['conditions']['desktop']['value'] ?? null;

		if ( ! is_array( $conditions ) || array() === $conditions || ! self::has_groups_condition( $conditions ) ) {
			return $content;
		}

		if ( ! class_exists( '\ET\Builder\Packages\Module\Options\Conditions\Conditions' ) ) {
			return self::groups_conditions_pass( $conditions ) ? $content : '';
		}

		$engine = new \ET\Builder\Packages\Module\Options\Conditions\Conditions();

		return $engine->is_displayable( $conditions ) ? $content : '';
	}

	/**
	 * Whether the condition list includes Groups membership.
	 *
	 * @param array<int, mixed> $conditions Saved condition items.
	 * @return bool
	 */
	private static function has_groups_condition( array $conditions ): bool {
		foreach ( $conditions as $condition ) {
			if ( is_array( $condition ) && 'groupsMembership' === ( $condition['conditionName'] ?? '' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Groups-only fallback when Divi's condition engine is unavailable.
	 *
	 * A single Groups condition is enough for the layouts this plugin migrates.
	 * Any other condition in the list is left as "show", matching Divi's default
	 * for a condition name it does not know.
	 *
	 * @param array<int, mixed> $conditions Saved condition items.
	 * @return bool
	 */
	private static function groups_conditions_pass( array $conditions ): bool {
		$display = true;

		foreach ( array_reverse( $conditions ) as $condition ) {
			if ( ! is_array( $condition ) ) {
				continue;
			}

			$settings = $condition['conditionSettings'] ?? array();
			$settings = is_array( $settings ) ? $settings : array();

			if ( isset( $settings['enableCondition'] ) && 'off' === $settings['enableCondition'] ) {
				continue;
			}

			$name = $condition['conditionName'] ?? '';
			$pass = 'groupsMembership' === $name
				? Groups_Divi_Conditions_Evaluator::matches( $settings )
				: true;

			$display  = $pass;
			$operator = $condition['operator'] ?? 'OR';

			if ( 'OR' === $operator && $pass ) {
				break;
			}

			if ( 'AND' === $operator && ! $pass ) {
				break;
			}
		}

		return $display;
	}
}
