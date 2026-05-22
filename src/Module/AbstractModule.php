<?php
declare( strict_types=1 );

namespace Underway\Module;

use Underway\Activation;

defined( 'ABSPATH' ) || exit;

/**
 * Base class providing per-module settings storage helpers.
 */
abstract class AbstractModule implements ModuleInterface {

	public function uses_ai(): bool {
		return false;
	}

	public function has_standalone_conflict(): bool {
		return false;
	}

	public function render_settings_section(): void {
		// Default: no per-module fields.
	}

	public function sanitize_settings( array $input ): array {
		return [];
	}

	/**
	 * Get a single per-module setting value.
	 */
	protected function get_setting( string $key, mixed $default = null ): mixed {
		$all     = (array) get_option( Activation::OPT_MODULE_SETTINGS, [] );
		$module  = (array) ( $all[ $this->id() ] ?? [] );
		return $module[ $key ] ?? $default;
	}

	/**
	 * Get all per-module settings.
	 *
	 * @return array<string,mixed>
	 */
	protected function get_settings(): array {
		$all = (array) get_option( Activation::OPT_MODULE_SETTINGS, [] );
		return (array) ( $all[ $this->id() ] ?? [] );
	}

	/**
	 * True if AI is enabled globally AND for this module.
	 */
	public function ai_enabled(): bool {
		if ( ! $this->uses_ai() ) {
			return false;
		}
		$ai = (array) get_option( Activation::OPT_AI, [] );
		if ( empty( $ai['master'] ) ) {
			return false;
		}
		$modules = (array) ( $ai['modules'] ?? [] );
		return ! empty( $modules[ $this->id() ] );
	}
}
