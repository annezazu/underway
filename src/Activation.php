<?php
declare( strict_types=1 );

namespace Underway;

defined( 'ABSPATH' ) || exit;

/**
 * Activation / deactivation handlers.
 *
 * Default behavior on first activation:
 *   - All modules enabled (user can deselect during onboarding).
 *   - `underway_needs_onboarding` flag set; admin redirect handled by Onboarding.
 *
 * Re-activation (after a deactivate/reactivate cycle):
 *   - Existing settings preserved; onboarding only re-shown if explicitly reset.
 */
final class Activation {

	public const OPT_MODULES        = 'underway_modules';
	public const OPT_AI             = 'underway_ai';
	public const OPT_MODULE_SETTINGS = 'underway_module_settings';
	public const OPT_ONBOARDED      = 'underway_onboarding_complete';
	public const OPT_REDIRECT       = 'underway_activation_redirect';

	public static function activate( bool $network_wide = false ): void {
		// Don't redirect on multisite network activation or bulk activations.
		$is_bulk = filter_input( INPUT_GET, 'activate-multi', FILTER_VALIDATE_BOOLEAN );

		if ( get_option( self::OPT_MODULES, null ) === null ) {
			update_option(
				self::OPT_MODULES,
				[
					'draft-sweeper'  => true,
					'future-drafts'  => true,
					'ideas-inbox'    => true,
					'habit-creator'  => true,
				],
				false
			);
		}

		if ( get_option( self::OPT_AI, null ) === null ) {
			// AI defaults OFF on fresh installs. Even if a connector is
			// configured, the user has to opt in — we don't assume they
			// want AI summaries / starter questions just because the
			// underlying capability exists.
			update_option(
				self::OPT_AI,
				[
					'master'  => false,
					'modules' => [
						'draft-sweeper' => false,
						'habit-creator' => false,
					],
				],
				false
			);
		}

		if ( get_option( self::OPT_MODULE_SETTINGS, null ) === null ) {
			update_option( self::OPT_MODULE_SETTINGS, [], false );
		}

		if ( ! get_option( self::OPT_ONBOARDED ) && ! $network_wide && ! $is_bulk ) {
			set_transient( self::OPT_REDIRECT, 1, 30 );
		}

		// Schedule Habit Creator cron if module enabled.
		$modules = (array) get_option( self::OPT_MODULES, [] );
		if ( ! empty( $modules['habit-creator'] ) && ! wp_next_scheduled( 'habit_creator_detect_patterns' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'habit_creator_detect_patterns' );
		}
	}

	public static function deactivate(): void {
		$timestamp = wp_next_scheduled( 'habit_creator_detect_patterns' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'habit_creator_detect_patterns' );
		}
		delete_transient( self::OPT_REDIRECT );
	}
}
