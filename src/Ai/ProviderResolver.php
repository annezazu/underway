<?php
declare( strict_types=1 );

namespace Underway\Ai;

defined( 'ABSPATH' ) || exit;

/**
 * Determines whether an AI provider is available via the WP Connectors API
 * (introduced alongside the WordPress AI Client in WP 7.0+).
 *
 * Returns false on older WP versions where the API doesn't exist — modules
 * that depend on AI should gracefully degrade.
 */
final class ProviderResolver {

	/**
	 * True iff at least one connector of type "ai_provider" is registered.
	 */
	public static function has_provider(): bool {
		if ( ! function_exists( 'wp_get_connectors' ) ) {
			return false;
		}

		$connectors = wp_get_connectors();
		if ( ! is_array( $connectors ) && ! is_iterable( $connectors ) ) {
			return false;
		}

		foreach ( $connectors as $connector ) {
			$type = is_object( $connector ) ? ( $connector->type ?? null ) : ( $connector['type'] ?? null );
			if ( $type === 'ai_provider' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Optional informational URL pointing the user to provider configuration.
	 * Falls back to a generic docs link if the AI admin page slug isn't present.
	 */
	public static function settings_url(): string {
		$candidates = [ 'ai-services', 'ai_services', 'wp-ai-client' ];
		foreach ( $candidates as $slug ) {
			$file = ABSPATH . 'wp-admin/admin.php?page=' . $slug;
			if ( function_exists( 'menu_page_url' ) ) {
				$url = menu_page_url( $slug, false );
				if ( ! empty( $url ) ) {
					return (string) $url;
				}
			}
		}
		return 'https://make.wordpress.org/core/2025/ai-client/';
	}
}
