<?php
declare( strict_types=1 );

namespace Underway\Module;

defined( 'ABSPATH' ) || exit;

/**
 * Contract every bundled module implements.
 */
interface ModuleInterface {

	/** Short, stable slug used in option keys and URLs (e.g. "draft-sweeper"). */
	public function id(): string;

	/** Human-readable name shown in admin UI. */
	public function label(): string;

	/** One-sentence description shown in onboarding and settings. */
	public function description(): string;

	/** True if this module has AI-enhanced features. */
	public function uses_ai(): bool;

	/**
	 * True if a standalone copy of the original plugin is already active.
	 * When true, the bundled module bails to avoid double registration.
	 */
	public function has_standalone_conflict(): bool;

	/** Register WP hooks. Called only when the module is enabled. */
	public function boot(): void;

	/**
	 * Render module-specific settings fields on the Underway settings page.
	 * May be a no-op.
	 */
	public function render_settings_section(): void;

	/**
	 * Sanitize submitted per-module settings.
	 *
	 * @param array<string,mixed> $input Raw submitted values for this module.
	 * @return array<string,mixed>
	 */
	public function sanitize_settings( array $input ): array;
}
