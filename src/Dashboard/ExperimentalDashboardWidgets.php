<?php
/**
 * Bridge between Underway's wp-build widget manifest and Gutenberg's
 * experimental Dashboard (Beta).
 *
 * Gutenberg's `gutenberg_register_widget_types()` only hydrates from its
 * own build manifest, so third-party plugins have to push their own
 * widget types into `WP_Widget_Type_Registry` and add their script
 * modules to the dashboard page's import map.
 *
 * @package Underway
 */

declare( strict_types=1 );

namespace Underway\Dashboard;

defined( 'ABSPATH' ) || exit;

final class ExperimentalDashboardWidgets {

	/**
	 * Boots the bridge if Gutenberg's experimental Dashboard is active.
	 *
	 * Cheap guard: the registry class only exists when Gutenberg loads
	 * `lib/experimental/dashboard-widgets/load.php`, which is gated by
	 * the "Dashboard (Beta)" experiment. When the experiment is off,
	 * everything below short-circuits.
	 */
	public static function boot(): void {
		if ( ! class_exists( '\WP_Widget_Type_Registry' ) ) {
			return;
		}

		add_action( 'init', [ self::class, 'register_widget_types' ], 20 );
		add_filter(
			'dashboard-wp-admin_boot_dependencies',
			[ self::class, 'add_modules_to_boot_dependencies' ]
		);
	}

	/**
	 * Pushes every Underway widget into `WP_Widget_Type_Registry`.
	 *
	 * Reads the build-time manifest produced by `wp-build` from
	 * `build/widgets/registry.php` and registers each entry with its
	 * resolved script-module handles.
	 */
	public static function register_widget_types(): void {
		if ( ! function_exists( 'underway_get_registered_widget_modules' ) ) {
			return;
		}
		$registry = \WP_Widget_Type_Registry::get_instance();
		foreach ( underway_get_registered_widget_modules() as $widget ) {
			if ( empty( $widget['name'] ) || $registry->is_registered( $widget['name'] ) ) {
				continue;
			}
			$registry->register(
				$widget['name'],
				[
					'render_module' => $widget['render_module'] ?? null,
					'widget_module' => $widget['widget_module'] ?? null,
					'presentation'  => $widget['presentation'] ?? null,
				]
			);
		}
	}

	/**
	 * Adds every Underway render/widget module to the dashboard page's
	 * import map as a dynamic dependency, so the dashboard can
	 * `import()` them on demand.
	 *
	 * Mirrors `gutenberg_add_widget_modules_to_dashboard_boot_deps()`.
	 *
	 * @param array<int, array<string, string>> $boot_dependencies
	 * @return array<int, array<string, string>>
	 */
	public static function add_modules_to_boot_dependencies( $boot_dependencies ): array {
		if ( ! function_exists( 'underway_get_registered_widget_modules' ) ) {
			return (array) $boot_dependencies;
		}
		foreach ( underway_get_registered_widget_modules() as $widget ) {
			if ( ! empty( $widget['render_module'] ) ) {
				$boot_dependencies[] = [
					'import' => 'dynamic',
					'id'     => $widget['render_module'],
				];
			}
			if ( ! empty( $widget['widget_module'] ) ) {
				$boot_dependencies[] = [
					'import' => 'dynamic',
					'id'     => $widget['widget_module'],
				];
			}
		}
		return (array) $boot_dependencies;
	}
}
