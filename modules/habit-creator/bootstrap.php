<?php
/**
 * Bundled bootstrap for Habit Creator.
 *
 * Mirrors the work the standalone habit-creator.php main file does, but:
 *   - skips Settings::init() (Underway provides the unified UI)
 *   - assumes the cron event is scheduled by Underway's Activation handler
 *
 * @package Underway
 */

declare( strict_types=1 );

namespace HabitCreator;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'HabitCreator\\VERSION' ) ) {
	define( 'HabitCreator\\VERSION', '0.4.49' );
}
if ( ! defined( 'HabitCreator\\CRON_HOOK' ) ) {
	define( 'HabitCreator\\CRON_HOOK', 'habit_creator_detect_patterns' );
}
if ( ! defined( 'HabitCreator\\TRANSIENT_KEY' ) ) {
	define( 'HabitCreator\\TRANSIENT_KEY', 'habit_creator_patterns_v2_' );
}
if ( ! defined( 'HabitCreator\\NONCE_ACTION' ) ) {
	define( 'HabitCreator\\NONCE_ACTION', 'habit_creator_action' );
}

require_once __DIR__ . '/includes/class-pattern-detector.php';
require_once __DIR__ . '/includes/class-ai-enhancer.php';
require_once __DIR__ . '/includes/class-draft-creator.php';
require_once __DIR__ . '/includes/class-dashboard-widget.php';
require_once __DIR__ . '/includes/class-settings.php';

// NOTE: deliberately skipping Settings::init() — Underway owns the UI.
add_action( 'wp_dashboard_setup', [ Dashboard_Widget::class, 'register' ] );
add_action( 'admin_enqueue_scripts', [ Dashboard_Widget::class, 'enqueue_assets' ] );
add_action( 'wp_ajax_habit_creator_create_draft', [ Draft_Creator::class, 'handle_ajax' ] );
add_action( 'wp_ajax_habit_creator_toggle_ai', [ Dashboard_Widget::class, 'handle_toggle_ai' ] );
add_action( CRON_HOOK, [ Pattern_Detector::class, 'run_for_all_authors' ] );
