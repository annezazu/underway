<?php
declare( strict_types=1 );

namespace Underway\Admin;

use Underway\Activation;
use Underway\Ai\ProviderResolver;
use Underway\ModuleRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Single settings page under Settings → Underway.
 *
 * Sections:
 *   - Widgets (toggle on/off)
 *   - AI (master + per-module, or notice if no provider)
 *   - Per-widget options (rendered by each module)
 */
final class SettingsPage {

	public const PAGE_SLUG    = 'underway';
	public const OPTION_GROUP = 'underway_settings';
	public const NONCE_ACTION = 'underway_settings_save';

	public function __construct( private ModuleRegistry $registry ) {}

	public function register(): void {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_post_underway_save_settings', [ $this, 'handle_save' ] );

		// Plugin row link.
		add_filter(
			'plugin_action_links_' . plugin_basename( UNDERWAY_FILE ),
			[ $this, 'add_settings_link' ]
		);
	}

	public function add_settings_link( array $links ): array {
		$url = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'underway' ) . '</a>'
		);
		return $links;
	}

	public function register_page(): void {
		add_options_page(
			__( 'Underway', 'underway' ),
			__( 'Underway', 'underway' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$modules      = $this->registry->all();
		$enabled_now  = (array) get_option( Activation::OPT_MODULES, [] );
		$ai_settings  = (array) get_option( Activation::OPT_AI, [] );
		$has_provider = ProviderResolver::has_provider();
		$has_ai_mod   = false;
		foreach ( $modules as $module ) {
			if ( $module->uses_ai() ) {
				$has_ai_mod = true;
				break;
			}
		}
		$icons   = [
			'draft-sweeper' => 'edit-large',
			'future-drafts' => 'clock',
			'ideas-inbox'   => 'lightbulb',
			'habit-creator' => 'chart-line',
		];
		$updated = filter_input( INPUT_GET, 'updated', FILTER_VALIDATE_BOOLEAN );
		?>
		<div class="underway-onboarding-wrap underway-settings-wrap">
			<div class="underway-onboarding">

				<header class="underway-onboarding__hero">
					<div class="underway-onboarding__hero-inner">
						<div class="underway-onboarding__eyebrow">
							<span class="underway-onboarding__logo" aria-hidden="true">
								<svg width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
									<path d="M10 3 L10 11.5" />
									<path d="M10 3.6 L14.6 11.2 L10 11.2 Z" fill="currentColor" stroke="none" />
									<path d="M4 12.5 L16 12.5 L14.2 15.5 L5.8 15.5 Z" fill="currentColor" stroke="none" />
									<path d="M3 17 Q5 16 7 17 T11 17 T15 17 T17 17" opacity=".7" />
								</svg>
							</span>
							<?php esc_html_e( 'Underway', 'underway' ); ?>
						</div>
						<h1><?php esc_html_e( 'Settings', 'underway' ); ?></h1>
						<p class="underway-onboarding__lead">
							<?php esc_html_e( 'Toggle widgets on or off, manage AI enhancements, and tune individual widget options. Changes take effect as soon as you save.', 'underway' ); ?>
						</p>
					</div>
				</header>

				<?php if ( $updated ) : ?>
					<div class="underway-success-banner">
						<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
						<span><?php esc_html_e( 'Settings saved.', 'underway' ); ?></span>
					</div>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="underway-onboarding__form">
					<?php wp_nonce_field( self::NONCE_ACTION ); ?>
					<input type="hidden" name="action" value="underway_save_settings" />

					<section class="underway-section">
						<h2 class="underway-section__title"><?php esc_html_e( 'Widgets', 'underway' ); ?></h2>
						<p class="underway-section__desc"><?php esc_html_e( 'Pick which dashboard widgets are active on this site.', 'underway' ); ?></p>

						<div class="underway-cards">
							<?php foreach ( $modules as $module ) :
								$id        = $module->id();
								$enabled   = ! empty( $enabled_now[ $id ] );
								$icon      = $icons[ $id ] ?? 'admin-generic';
								$conflict  = $module->has_standalone_conflict();
							?>
								<label class="underway-card<?php echo $conflict ? ' is-disabled' : ''; ?>">
									<input type="checkbox" name="modules[<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( $enabled ); ?> <?php disabled( $conflict ); ?> />
									<span class="underway-card__check" aria-hidden="true"></span>
									<span class="underway-card__icon" aria-hidden="true">
										<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
									</span>
									<span class="underway-card__body">
										<span class="underway-card__title">
											<?php echo esc_html( $module->label() ); ?>
											<?php if ( $module->uses_ai() ) : ?>
												<span class="underway-ai-badge <?php echo $has_provider ? '' : 'is-disabled'; ?>" title="<?php echo esc_attr( $has_provider ? __( 'AI features active.', 'underway' ) : __( 'Connect an AI provider to enable AI features.', 'underway' ) ); ?>">
													<?php esc_html_e( 'AI enhanced', 'underway' ); ?>
												</span>
											<?php endif; ?>
										</span>
										<span class="underway-card__desc"><?php echo esc_html( $module->description() ); ?></span>
										<?php if ( $conflict ) : ?>
											<span class="underway-card__conflict"><?php esc_html_e( 'A standalone plugin is active — the bundled version is disabled until you deactivate it.', 'underway' ); ?></span>
										<?php endif; ?>
									</span>
								</label>
							<?php endforeach; ?>
						</div>
					</section>

					<?php if ( $has_ai_mod ) : ?>
						<section class="underway-section">
							<h2 class="underway-section__title"><?php esc_html_e( 'AI enhancements', 'underway' ); ?></h2>
							<p class="underway-section__desc">
								<?php esc_html_e( 'AI is optional. Every widget works without it; connect a provider to unlock extras like draft summaries and writing prompts.', 'underway' ); ?>
							</p>

							<?php if ( ! $has_provider ) : ?>
								<div class="underway-empty-state" role="region" aria-labelledby="underway-no-provider-heading">
									<span class="underway-empty-state__icon" aria-hidden="true">
										<span class="dashicons dashicons-admin-plugins"></span>
									</span>
									<div class="underway-empty-state__body">
										<h3 id="underway-no-provider-heading" class="underway-empty-state__title">
											<?php esc_html_e( 'No AI provider connected yet', 'underway' ); ?>
										</h3>
										<p class="underway-empty-state__desc">
											<?php esc_html_e( 'AI-enhanced widgets are working today without AI. Connect a provider through the WordPress Connectors API to unlock draft summaries (Draft Sweeper) and writing prompts (Habit Creator).', 'underway' ); ?>
										</p>
										<p class="underway-empty-state__actions">
											<a class="button button-primary button-hero underway-onboarding__cta" href="<?php echo esc_url( ProviderResolver::settings_url() ); ?>">
												<?php esc_html_e( 'Open connector settings', 'underway' ); ?>
												<span class="dashicons dashicons-external" aria-hidden="true"></span>
											</a>
										</p>
									</div>
								</div>
							<?php else : ?>
							<div class="underway-panel">
								<div class="underway-panel__row">
									<div class="underway-panel__row-label">
										<strong><?php esc_html_e( 'Use AI globally', 'underway' ); ?></strong>
										<span class="underway-panel__hint"><?php esc_html_e( 'Master switch for AI across all enhanced widgets.', 'underway' ); ?></span>
									</div>
									<div class="underway-panel__row-control">
										<label class="underway-switch">
											<input type="checkbox" name="ai[master]" value="1" <?php checked( ! empty( $ai_settings['master'] ) ); ?> <?php disabled( ! $has_provider ); ?> />
											<span class="underway-switch__track" aria-hidden="true"><span class="underway-switch__thumb"></span></span>
										</label>
									</div>
								</div>

								<?php
								$ai_modules = (array) ( $ai_settings['modules'] ?? [] );
								foreach ( $modules as $module ) :
									if ( ! $module->uses_ai() ) {
										continue;
									}
									$checked = ! empty( $ai_modules[ $module->id() ] );
								?>
									<div class="underway-panel__row">
										<div class="underway-panel__row-label">
											<strong><?php echo esc_html( $module->label() ); ?></strong>
											<span class="underway-panel__hint"><?php echo esc_html( $module->description() ); ?></span>
										</div>
										<div class="underway-panel__row-control">
											<label class="underway-switch">
												<input type="checkbox" name="ai[modules][<?php echo esc_attr( $module->id() ); ?>]" value="1" <?php checked( $checked ); ?> <?php disabled( ! $has_provider ); ?> />
												<span class="underway-switch__track" aria-hidden="true"><span class="underway-switch__thumb"></span></span>
											</label>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<?php
					// Per-widget options (only when module is enabled and renders something).
					$module_panels = [];
					foreach ( $modules as $module ) {
						if ( ! $this->registry->is_enabled( $module->id() ) ) {
							continue;
						}
						ob_start();
						$module->render_settings_section();
						$body = trim( (string) ob_get_clean() );
						if ( $body === '' ) {
							continue;
						}
						$module_panels[ $module->id() ] = [
							'label' => $module->label(),
							'icon'  => $icons[ $module->id() ] ?? 'admin-generic',
							'body'  => $body,
						];
					}
					if ( $module_panels ) :
					?>
						<section class="underway-section">
							<h2 class="underway-section__title"><?php esc_html_e( 'Widget options', 'underway' ); ?></h2>
							<p class="underway-section__desc"><?php esc_html_e( 'Tune behavior for individual widgets.', 'underway' ); ?></p>

							<?php foreach ( $module_panels as $panel ) : ?>
								<details class="underway-module-options" open>
									<summary>
										<span class="underway-card__icon underway-card__icon--inline" aria-hidden="true">
											<span class="dashicons dashicons-<?php echo esc_attr( $panel['icon'] ); ?>"></span>
										</span>
										<span><?php echo esc_html( $panel['label'] ); ?></span>
									</summary>
									<div class="underway-module-options__body">
										<?php echo $panel['body']; // phpcs:ignore WordPress.Security.EscapeOutput -- Module escapes its own fields. ?>
									</div>
								</details>
							<?php endforeach; ?>
						</section>
					<?php endif; ?>

					<div class="underway-onboarding__actions">
						<button type="submit" class="button button-primary button-hero underway-onboarding__cta">
							<?php esc_html_e( 'Save changes', 'underway' ); ?>
						</button>
						<p class="underway-onboarding__hint">
							<?php
							printf(
								/* translators: %s is the plugin version. */
								esc_html__( 'Underway %s', 'underway' ),
								esc_html( UNDERWAY_VERSION )
							);
							?>
						</p>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	public function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do that.', 'underway' ) );
		}
		check_admin_referer( self::NONCE_ACTION );

		// --- Modules ---
		$submitted_modules = isset( $_POST['modules'] ) && is_array( $_POST['modules'] )
			? wp_unslash( $_POST['modules'] )
			: [];
		$next_modules = [];
		foreach ( $this->registry->all() as $module ) {
			if ( $module->has_standalone_conflict() ) {
				$next_modules[ $module->id() ] = false;
				continue;
			}
			$next_modules[ $module->id() ] = ! empty( $submitted_modules[ $module->id() ] );
		}
		$prev_modules = (array) get_option( Activation::OPT_MODULES, [] );
		update_option( Activation::OPT_MODULES, $next_modules, false );

		// --- AI ---
		$submitted_ai = isset( $_POST['ai'] ) && is_array( $_POST['ai'] )
			? wp_unslash( $_POST['ai'] )
			: [];
		$ai_modules = [];
		foreach ( $this->registry->all() as $module ) {
			if ( $module->uses_ai() ) {
				$ai_modules[ $module->id() ] = ! empty( $submitted_ai['modules'][ $module->id() ] );
			}
		}
		update_option(
			Activation::OPT_AI,
			[
				'master'  => ! empty( $submitted_ai['master'] ),
				'modules' => $ai_modules,
			],
			false
		);

		// --- Per-module settings ---
		$submitted_settings = isset( $_POST['module_settings'] ) && is_array( $_POST['module_settings'] )
			? wp_unslash( $_POST['module_settings'] )
			: [];
		$next_settings = [];
		foreach ( $this->registry->all() as $module ) {
			$raw   = (array) ( $submitted_settings[ $module->id() ] ?? [] );
			$clean = $module->sanitize_settings( $raw );
			if ( ! empty( $clean ) ) {
				$next_settings[ $module->id() ] = $clean;
			}
		}
		update_option( Activation::OPT_MODULE_SETTINGS, $next_settings, false );

		// Manage Habit Creator cron when its enabled state changes.
		$was_hc = ! empty( $prev_modules['habit-creator'] );
		$is_hc  = ! empty( $next_modules['habit-creator'] );
		if ( $is_hc && ! wp_next_scheduled( 'habit_creator_detect_patterns' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'habit_creator_detect_patterns' );
		} elseif ( ! $is_hc && $was_hc ) {
			$ts = wp_next_scheduled( 'habit_creator_detect_patterns' );
			if ( $ts ) {
				wp_unschedule_event( $ts, 'habit_creator_detect_patterns' );
			}
		}

		wp_safe_redirect( add_query_arg( [ 'updated' => 1 ], admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ) );
		exit;
	}
}
