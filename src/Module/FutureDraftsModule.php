<?php
declare( strict_types=1 );

namespace Underway\Module;

defined( 'ABSPATH' ) || exit;

final class FutureDraftsModule extends AbstractModule {

	public function id(): string {
		return 'future-drafts';
	}

	public function label(): string {
		return __( 'Future Drafts', 'underway' );
	}

	public function description(): string {
		return __( 'Capture an experience now as a tiny draft; the widget brings it back on the date you choose.', 'underway' );
	}

	public function has_standalone_conflict(): bool {
		return StandaloneDetector::is_active(
			'future-drafts/future-drafts.php',
			'FUTURE_DRAFTS_LOADED'
		);
	}

	public function boot(): void {
		// Don't double-load if standalone already registered the namespace.
		if ( class_exists( '\\FutureDrafts\\Plugin', false ) ) {
			return;
		}
		// Register a small autoloader scoped to this module's namespace.
		spl_autoload_register( static function ( string $class ): void {
			$prefix = 'FutureDrafts\\';
			if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
				return;
			}
			$relative = substr( $class, strlen( $prefix ) );
			$file     = UNDERWAY_DIR . '/modules/future-drafts/src/' . str_replace( '\\', '/', $relative ) . '.php';
			if ( is_file( $file ) ) {
				require_once $file;
			}
		} );
		if ( ! defined( 'FUTURE_DRAFTS_LOADED' ) ) {
			define( 'FUTURE_DRAFTS_LOADED', true );
		}
		// Pass our module's main file so plugins_url() resolves to modules/future-drafts/build/...
		$plugin_file = UNDERWAY_DIR . '/modules/future-drafts/future-drafts.php';
		( new \FutureDrafts\Plugin( $plugin_file ) )->register();
	}
}
