<?php
declare( strict_types=1 );

namespace Underway;

use Underway\Module\ModuleInterface;
use Underway\Module\DraftSweeperModule;
use Underway\Module\FutureDraftsModule;
use Underway\Module\HabitCreatorModule;
use Underway\Module\IdeasInboxModule;

defined( 'ABSPATH' ) || exit;

/**
 * Holds module definitions and answers enabled/disabled questions.
 */
final class ModuleRegistry {

	/** @var array<string,ModuleInterface> */
	private array $modules = [];

	public function __construct() {
		$this->register( new DraftSweeperModule() );
		$this->register( new FutureDraftsModule() );
		$this->register( new IdeasInboxModule() );
		$this->register( new HabitCreatorModule() );
	}

	public function register( ModuleInterface $module ): void {
		$this->modules[ $module->id() ] = $module;
	}

	/** @return array<string,ModuleInterface> */
	public function all(): array {
		return $this->modules;
	}

	public function get( string $id ): ?ModuleInterface {
		return $this->modules[ $id ] ?? null;
	}

	public function is_enabled( string $id ): bool {
		$opts = (array) get_option( Activation::OPT_MODULES, [] );
		return ! empty( $opts[ $id ] );
	}

	/** @return array<string,ModuleInterface> */
	public function enabled(): array {
		return array_filter(
			$this->modules,
			fn ( ModuleInterface $m ) => $this->is_enabled( $m->id() ) && ! $m->has_standalone_conflict()
		);
	}

	public function boot_enabled(): void {
		foreach ( $this->enabled() as $module ) {
			$module->boot();
		}
	}
}
