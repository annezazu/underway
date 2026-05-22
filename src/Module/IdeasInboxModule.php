<?php
declare( strict_types=1 );

namespace Underway\Module;

defined( 'ABSPATH' ) || exit;

final class IdeasInboxModule extends AbstractModule {

	public function id(): string {
		return 'ideas-inbox';
	}

	public function label(): string {
		return __( 'Ideas Inbox', 'underway' );
	}

	public function description(): string {
		return __( 'A per-user ideas inbox on your dashboard. Drop ideas now; convert any to a draft with one click.', 'underway' );
	}

	public function has_standalone_conflict(): bool {
		return \Underway\Module\StandaloneDetector::is_active(
			'ideas-dashboard-widget/ideas-dashboard-widget.php',
			'IDEAS_INBOX_VERSION'
		);
	}

	public function boot(): void {
		if ( defined( 'IDEAS_INBOX_VERSION' ) ) {
			return;
		}
		require_once UNDERWAY_DIR . '/modules/ideas-inbox/ideas-inbox.php';
	}
}
