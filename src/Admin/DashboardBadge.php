<?php
declare( strict_types=1 );

namespace Underway\Admin;

use Underway\ModuleRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the Underway sail logo next to each bundled dashboard widget's title
 * so site owners can tell at a glance which widgets come from this plugin.
 *
 * We run at `wp_dashboard_setup` priority 999 — after every module has
 * registered its widget — and rewrite the title in `$wp_meta_boxes` to a
 * span that pairs the logo with the original label. WordPress echoes the
 * title HTML directly into the metabox header, so inline SVG is supported.
 */
final class DashboardBadge {

	/** Map of widget metabox IDs that should receive the badge. */
	private const WIDGET_IDS = [
		'draft_sweeper_widget'  => true,
		'future_drafts_widget'  => true,
		'ideas_inbox_widget'    => true,
		'habit_creator_widget'  => true,
	];

	public function __construct( private ModuleRegistry $registry ) {}

	public function register(): void {
		add_action( 'wp_dashboard_setup', [ $this, 'decorate_widget_titles' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	public function enqueue_styles( string $hook ): void {
		if ( $hook !== 'index.php' ) {
			return;
		}
		wp_enqueue_style(
			'underway-dashboard',
			UNDERWAY_URL . 'assets/css/dashboard.css',
			[],
			UNDERWAY_VERSION
		);
	}

	public function decorate_widget_titles(): void {
		global $wp_meta_boxes;
		if ( ! is_array( $wp_meta_boxes ) || ! isset( $wp_meta_boxes['dashboard'] ) ) {
			return;
		}

		foreach ( $wp_meta_boxes['dashboard'] as $context => $priorities ) {
			foreach ( (array) $priorities as $priority => $boxes ) {
				foreach ( (array) $boxes as $id => $box ) {
					if ( ! isset( self::WIDGET_IDS[ $id ] ) ) {
						continue;
					}
					if ( ! isset( $box['title'] ) || ! is_string( $box['title'] ) ) {
						continue;
					}
					$wp_meta_boxes['dashboard'][ $context ][ $priority ][ $id ]['title'] =
						self::badge_html() . '<span class="underway-widget-title__label">' . $box['title'] . '</span>';
				}
			}
		}
	}

	/**
	 * Inline SVG of the Underway sail. Sized to match the dashboard widget
	 * title's line-height so it visually anchors to the text.
	 */
	private static function badge_html(): string {
		$tooltip = esc_attr__( 'Part of Underway', 'underway' );
		return '<span class="underway-widget-badge" aria-label="' . $tooltip . '" title="' . $tooltip . '">'
			. '<svg width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
			. '<path d="M10 3 L10 11.5" />'
			. '<path d="M10 3.6 L14.6 11.2 L10 11.2 Z" fill="currentColor" stroke="none" />'
			. '<path d="M4 12.5 L16 12.5 L14.2 15.5 L5.8 15.5 Z" fill="currentColor" stroke="none" />'
			. '<path d="M3 17 Q5 16 7 17 T11 17 T15 17 T17 17" opacity=".7" />'
			. '</svg>'
			. '</span>';
	}
}
