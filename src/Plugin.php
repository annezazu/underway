<?php
declare( strict_types=1 );

namespace Underway;

use Underway\Admin\DashboardBadge;
use Underway\Admin\Notices;
use Underway\Admin\Onboarding;
use Underway\Admin\SettingsPage;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin orchestrator. Boots once on `plugins_loaded`.
 */
final class Plugin {

	private static ?self $instance = null;

	private ModuleRegistry $registry;
	private SettingsPage   $settings;
	private Onboarding     $onboarding;
	private Notices        $notices;
	private DashboardBadge $dashboard_badge;

	public static function boot(): void {
		if ( self::$instance !== null ) {
			return;
		}
		self::$instance = new self();
		self::$instance->register();
	}

	public static function instance(): self {
		if ( self::$instance === null ) {
			self::boot();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->registry   = new ModuleRegistry();
		$this->settings   = new SettingsPage( $this->registry );
		$this->onboarding      = new Onboarding( $this->registry );
		$this->notices         = new Notices();
		$this->dashboard_badge = new DashboardBadge( $this->registry );
	}

	public function registry(): ModuleRegistry {
		return $this->registry;
	}

	private function register(): void {
		load_plugin_textdomain( 'underway', false, dirname( plugin_basename( UNDERWAY_FILE ) ) . '/languages' );

		// Boot each enabled module. Modules self-register their hooks.
		$this->registry->boot_enabled();

		// Admin UI.
		if ( is_admin() ) {
			$this->settings->register();
			$this->onboarding->register();
			$this->notices->register();
			$this->dashboard_badge->register();
		}
	}
}
