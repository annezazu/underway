<?php
declare( strict_types=1 );

namespace Underway\Module;

defined( 'ABSPATH' ) || exit;

final class HabitCreatorModule extends AbstractModule {

	public function id(): string {
		return 'habit-creator';
	}

	public function label(): string {
		return __( 'Habit Creator', 'underway' );
	}

	public function description(): string {
		return __( 'Spots recurring patterns in your archive (topics, tags, seasonal cadence) and nudges you to write the next installment.', 'underway' );
	}

	public function uses_ai(): bool {
		return true;
	}

	public function has_standalone_conflict(): bool {
		return StandaloneDetector::is_active(
			'habit-creator/habit-creator.php',
			'HABIT_CREATOR_LOADED'
		);
	}

	public function boot(): void {
		if ( defined( 'HabitCreator\\VERSION' ) ) {
			return; // Standalone already loaded.
		}
		if ( ! defined( 'UNDERWAY_BUNDLED' ) ) {
			define( 'UNDERWAY_BUNDLED', true );
		}
		if ( ! defined( 'HABIT_CREATOR_LOADED' ) ) {
			define( 'HABIT_CREATOR_LOADED', true );
		}
		require_once UNDERWAY_DIR . '/modules/habit-creator/bootstrap.php';
	}
}
