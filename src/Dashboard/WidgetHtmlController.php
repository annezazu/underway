<?php
/**
 * REST endpoint that returns each Underway widget's existing PHP-rendered
 * HTML so the React render modules used by the experimental Dashboard
 * can inject it verbatim.
 *
 * This is a demo bridge, not the final shape: it preserves every widget's
 * existing server-side render path while the new dashboard surface gets
 * proper React ports. Interactivity that relies on classic enqueued
 * scripts is not wired up here.
 *
 * @package Underway
 */

declare( strict_types=1 );

namespace Underway\Dashboard;

defined( 'ABSPATH' ) || exit;

final class WidgetHtmlController {

	private const ROUTE_NAMESPACE = 'underway/v1';

	public static function register(): void {
		add_action( 'rest_api_init', [ self::class, 'register_routes' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_widget_styles_on_dashboard' ] );
	}

	public static function register_routes(): void {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/widgets/(?P<slug>[a-z0-9-]+)/html',
			[
				'methods'             => 'GET',
				'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
				'callback'            => [ self::class, 'handle_render' ],
				'args'                => [
					'slug' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			]
		);
	}

	public static function handle_render( \WP_REST_Request $request ) {
		$slug = (string) $request->get_param( 'slug' );
		$html = self::render_for_slug( $slug );
		if ( null === $html ) {
			return new \WP_Error( 'underway_unknown_widget', 'Unknown widget', [ 'status' => 404 ] );
		}
		return [
			'html' => $html,
		];
	}

	/**
	 * Force-load each widget's classic dashboard CSS on the new Dashboard
	 * page so the injected HTML inherits its existing styling. The classic
	 * widget enqueue hooks only fire on `index.php`; the new page is
	 * `toplevel_page_dashboard-wp-admin`.
	 *
	 * @param string $hook
	 */
	public static function enqueue_widget_styles_on_dashboard( string $hook ): void {
		if ( 'toplevel_page_dashboard-wp-admin' !== $hook ) {
			return;
		}

		$plugin_url = plugin_dir_url( UNDERWAY_FILE );

		// The dashboard surface loads as script modules; classic
		// `wp.apiFetch` isn't pulled in by default. Force-enqueue it so
		// the React render modules can call REST.
		wp_enqueue_script( 'wp-api-fetch' );

		// Draft Sweeper.
		if ( file_exists( UNDERWAY_DIR . '/modules/draft-sweeper/assets/widget.css' ) ) {
			wp_enqueue_style(
				'underway-draft-sweeper-classic',
				$plugin_url . 'modules/draft-sweeper/assets/widget.css',
				[],
				UNDERWAY_VERSION
			);
		}

		// Future Drafts.
		if ( file_exists( UNDERWAY_DIR . '/modules/future-drafts/build/widget.css' ) ) {
			wp_enqueue_style(
				'underway-future-drafts-classic',
				$plugin_url . 'modules/future-drafts/build/widget.css',
				[ 'wp-components' ],
				UNDERWAY_VERSION
			);
		}
		// Future Drafts also needs its React bundle so the new dashboard
		// can call window.UnderwayFutureDrafts.mount(node) after injecting
		// the root div.
		$fd_asset_path = UNDERWAY_DIR . '/modules/future-drafts/build/widget.asset.php';
		if ( file_exists( UNDERWAY_DIR . '/modules/future-drafts/build/widget.js' ) ) {
			$asset = file_exists( $fd_asset_path )
				? require $fd_asset_path
				: [ 'dependencies' => [ 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ], 'version' => UNDERWAY_VERSION ];
			wp_enqueue_script(
				'underway-future-drafts-classic',
				$plugin_url . 'modules/future-drafts/build/widget.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);
			// Mirror the localize that the classic widget does so the
			// React app can read its bootstrap config.
			$rest_ns = class_exists( '\FutureDrafts\Rest\Controller' )
				? \FutureDrafts\Rest\Controller::NAMESPACE
				: 'future-drafts/v1';
			wp_localize_script(
				'underway-future-drafts-classic',
				'futureDrafts',
				[
					'restNamespace' => $rest_ns,
					'today'         => wp_date( 'Y-m-d' ),
					'subtitle'      => __( "Create a draft for your future self. We'll bring it back when you're ready.", 'underway' ),
				]
			);
		}

		// Habit Creator.
		if ( file_exists( UNDERWAY_DIR . '/modules/habit-creator/assets/widget.css' ) ) {
			wp_enqueue_style(
				'underway-habit-creator-classic',
				$plugin_url . 'modules/habit-creator/assets/widget.css',
				[],
				UNDERWAY_VERSION
			);
		}

		// Ideas Inbox.
		if ( file_exists( UNDERWAY_DIR . '/modules/ideas-inbox/assets/ideas-inbox.css' ) ) {
			wp_enqueue_style(
				'underway-ideas-inbox-classic',
				$plugin_url . 'modules/ideas-inbox/assets/ideas-inbox.css',
				[ 'dashicons', 'wp-components' ],
				UNDERWAY_VERSION
			);
		}
	}

	/**
	 * Dispatches per widget slug to the existing PHP render path.
	 */
	private static function render_for_slug( string $slug ): ?string {
		ob_start();
		switch ( $slug ) {
			case 'draft-sweeper':
				self::render_draft_sweeper();
				break;
			case 'future-drafts':
				self::render_future_drafts();
				break;
			case 'habit-creator':
				self::render_habit_creator();
				break;
			case 'ideas-inbox':
				self::render_ideas_inbox();
				break;
			default:
				ob_end_clean();
				return null;
		}
		return (string) ob_get_clean();
	}

	private static function render_draft_sweeper(): void {
		if ( ! class_exists( '\DraftSweeper\Plugin' ) || ! class_exists( '\DraftSweeper\Dashboard\DashboardWidget' ) ) {
			return;
		}
		$plugin = \DraftSweeper\Plugin::instance();
		( new \DraftSweeper\Dashboard\DashboardWidget( $plugin ) )->render();
	}

	private static function render_future_drafts(): void {
		// Classic widget echoes only the mount node — the React app lives in
		// modules/future-drafts/build/widget.js and exposes
		// window.UnderwayFutureDrafts.mount for the new dashboard surface
		// to call after injecting this HTML.
		echo '<div id="future-drafts-root"></div>';
	}

	private static function render_habit_creator(): void {
		if ( class_exists( '\HabitCreator\Dashboard_Widget' ) ) {
			\HabitCreator\Dashboard_Widget::render();
		}
	}

	private static function render_ideas_inbox(): void {
		if ( function_exists( 'ideas_inbox_render_widget' ) ) {
			ideas_inbox_render_widget();
		}
	}
}
