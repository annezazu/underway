<?php
declare( strict_types=1 );

namespace Underway\Admin;

use Underway\Activation;
use Underway\Ai\ProviderResolver;
use Underway\ModuleRegistry;

defined( 'ABSPATH' ) || exit;

/**
 * Post-activation onboarding wizard. Lets the user opt out of any widgets
 * before they appear on the dashboard.
 */
final class Onboarding {

	public const PAGE_SLUG    = 'underway-onboarding';
	public const NONCE_ACTION = 'underway_onboarding';

	public function __construct( private ModuleRegistry $registry ) {}

	public function register(): void {
		add_action( 'admin_init', [ $this, 'maybe_redirect' ] );
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_post_underway_save_onboarding', [ $this, 'handle_save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function maybe_redirect(): void {
		if ( ! get_transient( Activation::OPT_REDIRECT ) ) {
			return;
		}
		delete_transient( Activation::OPT_REDIRECT );

		if ( wp_doing_ajax() || is_network_admin() ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
		exit;
	}

	public function register_page(): void {
		// Hidden submenu: registered with null parent so it's reachable by URL but absent from the menu.
		add_submenu_page(
			'',
			__( 'Welcome to Underway', 'underway' ),
			__( 'Welcome to Underway', 'underway' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, self::PAGE_SLUG ) === false && strpos( $hook, SettingsPage::PAGE_SLUG ) === false ) {
			return;
		}
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style(
			'underway-admin',
			UNDERWAY_URL . 'assets/css/admin.css',
			[ 'dashicons' ],
			UNDERWAY_VERSION
		);
		if ( strpos( $hook, SettingsPage::PAGE_SLUG ) !== false ) {
			wp_enqueue_script(
				'underway-settings',
				UNDERWAY_URL . 'assets/js/settings.js',
				[],
				UNDERWAY_VERSION,
				true
			);
		}
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$modules     = $this->registry->all();
		$enabled_now = (array) get_option( Activation::OPT_MODULES, [] );
		$has_ai      = ProviderResolver::has_provider();
		$icons       = [
			'draft-sweeper' => 'edit-large',
			'future-drafts' => 'clock',
			'ideas-inbox'   => 'lightbulb',
			'habit-creator' => 'chart-line',
		];
		?>
		<div class="underway-onboarding-wrap">
			<div class="underway-onboarding">

				<header class="underway-onboarding__hero">
					<div class="underway-onboarding__hero-inner">
						<div class="underway-onboarding__eyebrow">
							<span class="underway-onboarding__logo" aria-hidden="true">
								<svg width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
									<!-- mast -->
									<path d="M10 3 L10 11.5" />
									<!-- sail -->
									<path d="M10 3.6 L14.6 11.2 L10 11.2 Z" fill="currentColor" stroke="none" />
									<!-- hull -->
									<path d="M4 12.5 L16 12.5 L14.2 15.5 L5.8 15.5 Z" fill="currentColor" stroke="none" />
									<!-- wave -->
									<path d="M3 17 Q5 16 7 17 T11 17 T15 17 T17 17" opacity=".7" />
								</svg>
							</span>
							<?php esc_html_e( 'Underway', 'underway' ); ?>
						</div>
						<h1><?php esc_html_e( 'Welcome aboard.', 'underway' ); ?></h1>
						<p class="underway-onboarding__lead">
							<?php esc_html_e( 'Four small dashboard widgets that help your writing get underway — from a fleeting idea to a published post. Pick what you want now; change your mind anytime from Settings → Underway.', 'underway' ); ?>
						</p>
					</div>
				</header>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="underway-onboarding__form">
					<?php wp_nonce_field( self::NONCE_ACTION ); ?>
					<input type="hidden" name="action" value="underway_save_onboarding" />

					<div class="underway-cards">
						<?php foreach ( $modules as $module ) :
							$id      = $module->id();
							$checked = ! isset( $enabled_now[ $id ] ) || ! empty( $enabled_now[ $id ] );
							$icon    = $icons[ $id ] ?? 'admin-generic';
						?>
							<label class="underway-card">
								<input type="checkbox" name="modules[<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( $checked ); ?> />
								<span class="underway-card__check" aria-hidden="true"></span>
								<span class="underway-card__icon" aria-hidden="true">
									<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
								</span>
								<span class="underway-card__body">
									<span class="underway-card__title">
										<?php echo esc_html( $module->label() ); ?>
										<?php if ( $module->uses_ai() ) : ?>
											<span class="underway-ai-badge <?php echo $has_ai ? '' : 'is-disabled'; ?>" title="<?php echo esc_attr( $has_ai ? __( 'Includes AI-enhanced features.', 'underway' ) : __( 'AI features available once an AI provider is connected.', 'underway' ) ); ?>">
												<?php esc_html_e( 'AI enhanced', 'underway' ); ?>
											</span>
										<?php endif; ?>
									</span>
									<span class="underway-card__desc"><?php echo esc_html( $module->description() ); ?></span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>

					<?php if ( ! $has_ai ) : ?>
						<div class="underway-ai-note">
							<span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
							<span>
								<?php esc_html_e( 'No AI provider is connected yet. AI-enhanced widgets work without it; connect a provider later to unlock the extras.', 'underway' ); ?>
							</span>
						</div>
					<?php endif; ?>

					<div class="underway-onboarding__actions">
						<button type="submit" class="button button-primary button-hero underway-onboarding__cta">
							<?php esc_html_e( 'Get started', 'underway' ); ?>
							<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
						</button>
						<p class="underway-onboarding__hint">
							<?php esc_html_e( 'You can always change these in Settings → Underway.', 'underway' ); ?>
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

		$submitted = isset( $_POST['modules'] ) && is_array( $_POST['modules'] )
			? wp_unslash( $_POST['modules'] )
			: [];

		$next = [];
		foreach ( $this->registry->all() as $module ) {
			$next[ $module->id() ] = ! empty( $submitted[ $module->id() ] );
		}

		update_option( Activation::OPT_MODULES, $next, false );
		update_option( Activation::OPT_ONBOARDED, time(), false );

		wp_safe_redirect( admin_url( 'index.php' ) );
		exit;
	}
}
