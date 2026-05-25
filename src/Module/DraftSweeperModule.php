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

	/**
	 * Named score-weight presets. Each maps to a (completeness, recency, relevance)
	 * triple that the deeper scoring algorithm already understands. The default
	 * preset is `mix`, which matches the original balanced weights.
	 *
	 * @return array<string, array{label:string, desc:string, weights:array{0:float,1:float,2:float}}>
	 */
	private function presets(): array {
		return [
			'finish' => [
				'label'   => __( 'Finish what\'s almost done', 'underway' ),
				'desc'    => __( 'Surface drafts you\'re closest to publishing.', 'underway' ),
				'weights' => [ 0.7, 0.2, 0.1 ],
			],
			'pickup' => [
				'label'   => __( 'Pick up where I left off', 'underway' ),
				'desc'    => __( 'Surface drafts you touched most recently.', 'underway' ),
				'weights' => [ 0.2, 0.7, 0.1 ],
			],
			'theme' => [
				'label'   => __( 'Stay on a theme', 'underway' ),
				'desc'    => __( 'Surface drafts that match latest writing.', 'underway' ),
				'weights' => [ 0.1, 0.2, 0.7 ],
			],
			'mix' => [
				'label'   => __( 'A mix of all three', 'underway' ),
				'desc'    => __( 'Balance completeness, recency, and theme.', 'underway' ),
				'weights' => [ 0.5, 0.2, 0.3 ],
			],
		];
	}

	/**
	 * Detect which preset (if any) the stored weights match, allowing small
	 * floating-point drift from normalization.
	 */
	private function detect_preset( float $c, float $r, float $v ): string {
		foreach ( $this->presets() as $key => $preset ) {
			[ $pc, $pr, $pv ] = $preset['weights'];
			if ( abs( $c - $pc ) < 0.02 && abs( $r - $pr ) < 0.02 && abs( $v - $pv ) < 0.02 ) {
				return $key;
			}
		}
		return 'custom';
	}

	public function render_settings_section(): void {
		$opts         = $this->get_settings();
		$scope        = (string) ( $opts['scope'] ?? 'mine' );
		$completeness = (float)  ( $opts['completeness'] ?? 0.5 );
		$recency      = (float)  ( $opts['recency']      ?? 0.2 );
		$relevance    = (float)  ( $opts['relevance']    ?? 0.3 );
		$current      = isset( $opts['preset'] ) && is_string( $opts['preset'] )
			? (string) $opts['preset']
			: $this->detect_preset( $completeness, $recency, $relevance );
		$presets      = $this->presets();
		?>
		<div class="underway-ds-settings">
		<div class="underway-field-group">
			<label class="underway-field-group__label" for="ds-scope"><?php esc_html_e( 'Drafts to surface', 'underway' ); ?></label>
			<select id="ds-scope" name="module_settings[draft-sweeper][scope]">
				<option value="mine" <?php selected( $scope, 'mine' ); ?>><?php esc_html_e( 'Just my drafts', 'underway' ); ?></option>
				<option value="all"  <?php selected( $scope, 'all' ); ?>><?php esc_html_e( 'Drafts by anyone on this site', 'underway' ); ?></option>
			</select>
		</div>

		<div class="underway-field-group">
			<span class="underway-field-group__label"><?php esc_html_e( 'What should Draft Sweeper prioritize?', 'underway' ); ?></span>
			<div class="underway-preset-list" role="radiogroup" aria-label="<?php esc_attr_e( 'Draft Sweeper priority', 'underway' ); ?>">
				<?php foreach ( $presets as $key => $preset ) : ?>
					<label class="underway-preset">
						<input type="radio" name="module_settings[draft-sweeper][preset]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $current, $key ); ?> />
						<span class="underway-preset__check" aria-hidden="true"></span>
						<span class="underway-preset__body">
							<span class="underway-preset__title"><?php echo esc_html( $preset['label'] ); ?></span>
							<span class="underway-preset__desc"><?php echo esc_html( $preset['desc'] ); ?></span>
						</span>
					</label>
				<?php endforeach; ?>
				<label class="underway-preset">
					<input type="radio" name="module_settings[draft-sweeper][preset]" value="custom" <?php checked( $current, 'custom' ); ?> />
					<span class="underway-preset__check" aria-hidden="true"></span>
					<span class="underway-preset__body">
						<span class="underway-preset__title"><?php esc_html_e( 'Custom mix', 'underway' ); ?></span>
						<span class="underway-preset__desc"><?php esc_html_e( 'Set the weights yourself.', 'underway' ); ?></span>
					</span>
				</label>
			</div>
		</div>

		<div class="underway-advanced">
			<div class="underway-advanced__header">
				<strong><?php esc_html_e( 'Set your weighting', 'underway' ); ?></strong>
				<span class="underway-advanced__desc">
					<?php esc_html_e( 'Fine-tune the three signals Draft Sweeper uses to rank drafts.', 'underway' ); ?>
				</span>
			</div>
			<div class="underway-advanced__grid">
				<label>
					<span><?php esc_html_e( 'Completeness', 'underway' ); ?></span>
					<input type="number" step="0.05" min="0" max="1" name="module_settings[draft-sweeper][completeness]" value="<?php echo esc_attr( (string) $completeness ); ?>" />
				</label>
				<label>
					<span><?php esc_html_e( 'Recency', 'underway' ); ?></span>
					<input type="number" step="0.05" min="0" max="1" name="module_settings[draft-sweeper][recency]" value="<?php echo esc_attr( (string) $recency ); ?>" />
				</label>
				<label>
					<span><?php esc_html_e( 'Topical relevance', 'underway' ); ?></span>
					<input type="number" step="0.05" min="0" max="1" name="module_settings[draft-sweeper][relevance]" value="<?php echo esc_attr( (string) $relevance ); ?>" />
				</label>
			</div>
			<p class="underway-advanced__hint"><?php esc_html_e( 'Values are normalized to sum to 1 on save.', 'underway' ); ?></p>
		</div>
		</div>
		<?php
	}

	public function sanitize_settings( array $input ): array {
		$scope    = ( ( $input['scope'] ?? 'mine' ) === 'all' ) ? 'all' : 'mine';
		$presets  = $this->presets();
		$preset   = isset( $input['preset'] ) && is_string( $input['preset'] ) ? $input['preset'] : 'mix';

		if ( isset( $presets[ $preset ] ) ) {
			[ $c, $r, $v ] = $presets[ $preset ]['weights'];
		} else {
			$preset = 'custom';
			$c = max( 0.0, min( 1.0, (float) ( $input['completeness'] ?? 0.5 ) ) );
			$r = max( 0.0, min( 1.0, (float) ( $input['recency']      ?? 0.2 ) ) );
			$v = max( 0.0, min( 1.0, (float) ( $input['relevance']    ?? 0.3 ) ) );
			$sum = $c + $r + $v;
			if ( $sum > 0 ) {
				$c /= $sum;
				$r /= $sum;
				$v /= $sum;
			}
		}

		return [
			'scope'        => $scope,
			'preset'       => $preset,
			'completeness' => $c,
			'recency'      => $r,
			'relevance'    => $v,
		];
	}
}
