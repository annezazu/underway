<?php
declare( strict_types=1 );

namespace Underway\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Detects whether a standalone copy of a bundled plugin is active, so the
 * bundled module can step aside and avoid double-registering widgets/hooks.
 */
final class StandaloneDetector {

	/**
	 * @param string $basename       e.g. "ideas-dashboard-widget/ideas-dashboard-widget.php"
	 * @param string $marker_const   A constant the standalone defines on load.
	 */
	public static function is_active( string $basename, string $marker_const ): bool {
		// Site-level active plugins.
		$active = (array) get_option( 'active_plugins', [] );
		if ( in_array( $basename, $active, true ) ) {
			return true;
		}

		// Network-active plugins (multisite).
		if ( is_multisite() ) {
			$network_active = (array) get_site_option( 'active_sitewide_plugins', [] );
			if ( isset( $network_active[ $basename ] ) ) {
				return true;
			}
		}

		return false;
	}
}
