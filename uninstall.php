<?php
/**
 * Uninstall handler. Removes Underway's own options + scheduled events.
 *
 * Module-internal user content (post meta, user meta) is intentionally
 * left in place — those are user-authored ideas, drafts, and meta.
 *
 * @package Underway
 */

declare( strict_types=1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = [
	'underway_modules',
	'underway_ai',
	'underway_module_settings',
	'underway_onboarding_complete',
];

foreach ( $options as $option ) {
	delete_option( $option );
	if ( is_multisite() ) {
		delete_site_option( $option );
	}
}

delete_transient( 'underway_activation_redirect' );

$timestamp = wp_next_scheduled( 'habit_creator_detect_patterns' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'habit_creator_detect_patterns' );
}
