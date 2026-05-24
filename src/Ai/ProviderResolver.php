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
	 * True iff at least one AI provider is both *registered* and *configured*.
	 *
	 * Registered alone is not enough: many AI Client plugins pre-register
	 * provider types (OpenAI, Anthropic, etc.) before any credentials exist.
	 * We also require either a non-empty stored API key (via the connector's
	 * declared `authentication.setting_name`) or a matching `*_API_KEY` env var.
	 */
	public static function has_provider(): bool {
		if ( ! function_exists( 'wp_get_connectors' ) ) {
			return false;
		}

		$connectors = wp_get_connectors();
		if ( ! is_iterable( $connectors ) ) {
			return false;
		}

		foreach ( $connectors as $id => $connector ) {
			$conn = is_object( $connector ) ? (array) $connector : (array) $connector;
			if ( ( $conn['type'] ?? '' ) !== 'ai_provider' ) {
				continue;
			}
			$auth = (array) ( $conn['authentication'] ?? [] );
			if ( ( $auth['method'] ?? '' ) !== 'api_key' ) {
				// Unknown auth scheme — assume connector handles its own credential check.
				return true;
			}
			if ( self::has_key( (string) $id, $auth['setting_name'] ?? null ) ) {
				return true;
			}
		}

		return false;
	}

	private static function has_key( string $id, ?string $setting_name ): bool {
		if ( $setting_name !== null && $setting_name !== '' ) {
			$option = get_option( $setting_name );
			if ( is_string( $option ) && $option !== '' ) {
				return true;
			}
		}
		$env = getenv( strtoupper( $id ) . '_API_KEY' );
		return is_string( $env ) && $env !== '';
	}

	/**
	 * Best-effort URL pointing the user at the AI connector configuration screen.
	 *
	 * Walks the global menu structure for any submenu URL containing the word
	 * "connector" so we discover wherever the active AI-client plugin chose to
	 * mount its admin. Falls back to known slugs, then to the public docs page.
	 */
	public static function settings_url(): string {
		// 1) Scan the registered admin submenu for a Connectors page the
		// current user can actually access.
		global $submenu;
		if ( is_array( $submenu ) ) {
			foreach ( $submenu as $parent => $items ) {
				foreach ( (array) $items as $item ) {
					$cap   = isset( $item[1] ) ? (string) $item[1] : '';
					$title = isset( $item[0] ) ? wp_strip_all_tags( (string) $item[0] ) : '';
					$slug  = isset( $item[2] ) ? (string) $item[2] : '';
					if ( $slug === '' ) {
						continue;
					}
					$matches = stripos( $title, 'connector' ) !== false
						|| stripos( $slug, 'connector' ) !== false;
					if ( ! $matches ) {
						continue;
					}
					if ( $cap !== '' && ! current_user_can( $cap ) ) {
						continue;
					}
					return self::build_admin_url( (string) $parent, $slug );
				}
			}
		}

		// 2) Known slugs from common AI-client plugins.
		if ( function_exists( 'menu_page_url' ) ) {
			foreach ( [ 'ai-services', 'ai_services', 'wp-ai-client', 'wp-connectors' ] as $slug ) {
				$url = menu_page_url( $slug, false );
				if ( ! empty( $url ) ) {
					return (string) $url;
				}
			}
		}

		// 3) Nothing usable found — send them to Plugins → Add New filtered for AI.
		if ( current_user_can( 'install_plugins' ) ) {
			return admin_url( 'plugin-install.php?s=ai+services&tab=search&type=term' );
		}

		// 4) Last resort: public docs.
		return 'https://make.wordpress.org/core/2025/ai-client/';
	}

	/**
	 * Build an admin URL from a parent slug + submenu slug, handling the case
	 * where the submenu slug is itself a wp-admin file (ends in .php).
	 */
	private static function build_admin_url( string $parent, string $slug ): string {
		if ( str_ends_with( $slug, '.php' ) ) {
			// Direct admin file (e.g. options-connectors.php).
			return admin_url( $slug );
		}
		$parent_file = str_ends_with( $parent, '.php' ) ? $parent : 'admin.php';
		return admin_url( $parent_file . '?page=' . $slug );
	}
}
