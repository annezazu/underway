<?php
declare( strict_types=1 );

namespace Underway\Module;

defined( 'ABSPATH' ) || exit;

final class DraftSweeperModule extends AbstractModule {

	public function id(): string {
		return 'draft-sweeper';
	}

	public function label(): string {
		return __( 'Draft Sweeper', 'underway' );
	}

	public function description(): string {
		return __( 'Resurfaces abandoned drafts, scored by completeness, recency, and topical relevance. Optional AI summary on top.', 'underway' );
	}

	public function uses_ai(): bool {
		return true;
	}

	public function has_standalone_conflict(): bool {
		return StandaloneDetector::is_active(
			'draft-sweeper/draft-sweeper.php',
			'DRAFT_SWEEPER_LOADED'
		);
	}

	public function boot(): void {
		if ( class_exists( '\\DraftSweeper\\Plugin', false ) ) {
			return;
		}

		spl_autoload_register( static function ( string $class ): void {
			$prefix = 'DraftSweeper\\';
			if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
				return;
			}
			$relative = substr( $class, strlen( $prefix ) );
			$file     = UNDERWAY_DIR . '/modules/draft-sweeper/src/' . str_replace( '\\', '/', $relative ) . '.php';
			if ( is_file( $file ) ) {
				require_once $file;
			}
		} );

		if ( ! defined( 'UNDERWAY_BUNDLED' ) ) {
			define( 'UNDERWAY_BUNDLED', true );
		}
		if ( ! defined( 'DRAFT_SWEEPER_LOADED' ) ) {
			define( 'DRAFT_SWEEPER_LOADED', true );
		}

		$plugin_file = UNDERWAY_DIR . '/modules/draft-sweeper/draft-sweeper.php';
		\DraftSweeper\Plugin::boot( $plugin_file );
	}

	public function render_settings_section(): void {
		$opts = $this->get_settings();
		$scope        = (string) ( $opts['scope'] ?? 'mine' );
		$completeness = (float)  ( $opts['completeness'] ?? 0.5 );
		$recency      = (float)  ( $opts['recency']      ?? 0.2 );
		$relevance    = (float)  ( $opts['relevance']    ?? 0.3 );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="ds-scope"><?php esc_html_e( 'Drafts to surface', 'underway' ); ?></label></th>
				<td>
					<select id="ds-scope" name="module_settings[draft-sweeper][scope]">
						<option value="mine" <?php selected( $scope, 'mine' ); ?>><?php esc_html_e( 'My drafts only', 'underway' ); ?></option>
						<option value="all"  <?php selected( $scope, 'all' ); ?>><?php esc_html_e( 'All site drafts', 'underway' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Score weights', 'underway' ); ?></th>
				<td>
					<p><label><?php esc_html_e( 'Completeness', 'underway' ); ?>
						<input type="number" step="0.05" min="0" max="1" name="module_settings[draft-sweeper][completeness]" value="<?php echo esc_attr( (string) $completeness ); ?>" />
					</label></p>
					<p><label><?php esc_html_e( 'Recency', 'underway' ); ?>
						<input type="number" step="0.05" min="0" max="1" name="module_settings[draft-sweeper][recency]" value="<?php echo esc_attr( (string) $recency ); ?>" />
					</label></p>
					<p><label><?php esc_html_e( 'Topical relevance', 'underway' ); ?>
						<input type="number" step="0.05" min="0" max="1" name="module_settings[draft-sweeper][relevance]" value="<?php echo esc_attr( (string) $relevance ); ?>" />
					</label></p>
					<p class="description"><?php esc_html_e( 'Values are normalized to sum to 1 on save.', 'underway' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function sanitize_settings( array $input ): array {
		$scope = ( ( $input['scope'] ?? 'mine' ) === 'all' ) ? 'all' : 'mine';
		$c = max( 0.0, min( 1.0, (float) ( $input['completeness'] ?? 0.5 ) ) );
		$r = max( 0.0, min( 1.0, (float) ( $input['recency']      ?? 0.2 ) ) );
		$v = max( 0.0, min( 1.0, (float) ( $input['relevance']    ?? 0.3 ) ) );
		$sum = $c + $r + $v;
		if ( $sum > 0 ) {
			$c /= $sum;
			$r /= $sum;
			$v /= $sum;
		}
		return [
			'scope'        => $scope,
			'completeness' => $c,
			'recency'      => $r,
			'relevance'    => $v,
		];
	}
}
