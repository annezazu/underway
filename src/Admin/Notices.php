<?php
declare( strict_types=1 );

namespace Underway\Admin;

use Underway\Module\ModuleInterface;
use Underway\Plugin;
use Underway\Activation;

defined( 'ABSPATH' ) || exit;

/**
 * Admin notices, primarily for standalone-plugin conflicts.
 */
final class Notices {

	public function register(): void {
		add_action( 'admin_notices', [ $this, 'render_conflict_notice' ] );
	}

	public function render_conflict_notice(): void {
		$conflicts = [];
		foreach ( Plugin::instance()->registry()->all() as $module ) {
			$opts = (array) get_option( Activation::OPT_MODULES, [] );
			if ( empty( $opts[ $module->id() ] ) ) {
				continue;
			}
			if ( $module->has_standalone_conflict() ) {
				$conflicts[] = $module;
			}
		}
		if ( ! $conflicts ) {
			return;
		}
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Underway:', 'underway' ); ?></strong>
				<?php esc_html_e( 'The following standalone plugins are active and conflict with Underway modules. The bundled versions are disabled until you deactivate the standalone plugin:', 'underway' ); ?>
			</p>
			<ul style="list-style: disc; margin-left: 1.5em;">
				<?php foreach ( $conflicts as $module ) : ?>
					<li><?php echo esc_html( $module->label() ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
