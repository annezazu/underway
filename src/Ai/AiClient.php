<?php
declare( strict_types=1 );

namespace Underway\Ai;

defined( 'ABSPATH' ) || exit;

/**
 * Thin wrapper around the two surfaces WordPress exposes for the AI Client:
 *   - The fluent helper `wp_ai_client_prompt( $prompt )->generate_text()`
 *   - The static class `\WordPress\AiClient\AiClient::generateText( [...] )`
 *
 * Either may be present depending on plugin version; we try the fluent helper
 * first and fall back to the class. If neither is available, returns null and
 * callers should fall back to non-AI behavior.
 */
final class AiClient {

	/**
	 * Generate a short text completion.
	 *
	 * @param string              $prompt  Prompt text.
	 * @param array<string,mixed> $options Optional config (max_tokens, model, etc.) passed through when supported.
	 * @return string|null Generated text, or null on failure / when no AI is available.
	 */
	public static function generate_text( string $prompt, array $options = [] ): ?string {
		if ( ! ProviderResolver::has_provider() ) {
			return null;
		}

		// 1) Fluent helper.
		if ( function_exists( 'wp_ai_client_prompt' ) ) {
			try {
				$builder = wp_ai_client_prompt( $prompt );
				if ( isset( $options['max_tokens'] ) && method_exists( $builder, 'max_tokens' ) ) {
					$builder = $builder->max_tokens( (int) $options['max_tokens'] );
				}
				$result = $builder->generate_text();
				$text   = self::extract_text( $result );
				if ( $text !== null ) {
					return $text;
				}
			} catch ( \Throwable $e ) {
				// Fall through to next surface.
				error_log( '[Underway] wp_ai_client_prompt failed: ' . $e->getMessage() );
			}
		}

		// 2) Static class surface.
		if ( class_exists( '\\WordPress\\AiClient\\AiClient' ) ) {
			try {
				$config = array_merge( [ 'prompt' => $prompt ], $options );
				$result = \WordPress\AiClient\AiClient::generateText( $config );
				return self::extract_text( $result );
			} catch ( \Throwable $e ) {
				error_log( '[Underway] AiClient::generateText failed: ' . $e->getMessage() );
			}
		}

		return null;
	}

	private static function extract_text( mixed $result ): ?string {
		if ( is_string( $result ) ) {
			$trimmed = trim( $result );
			return $trimmed === '' ? null : $trimmed;
		}
		if ( is_object( $result ) ) {
			foreach ( [ 'text', 'getText', 'toString', '__toString' ] as $accessor ) {
				if ( method_exists( $result, $accessor ) ) {
					$value = $result->{$accessor}();
					if ( is_string( $value ) && trim( $value ) !== '' ) {
						return trim( $value );
					}
				}
			}
			if ( isset( $result->text ) && is_string( $result->text ) ) {
				return trim( $result->text ) ?: null;
			}
		}
		if ( is_array( $result ) ) {
			$candidate = $result['text'] ?? $result['content'] ?? $result['output'] ?? null;
			if ( is_string( $candidate ) ) {
				return trim( $candidate ) ?: null;
			}
		}
		return null;
	}
}
