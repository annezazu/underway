import { __ } from '@wordpress/i18n';
import './render.scss';

interface Props {
	attributes?: Record< string, unknown >;
}

export default function DraftSweeperWidget( _props: Props ) {
	return (
		<div className="underway-widget">
			<p className="underway-widget__lede">
				{ __(
					'Surfaces one forgotten draft a day, picked from what you have already started.',
					'underway'
				) }
			</p>
			<p className="underway-widget__hint">
				{ __(
					'Demo placeholder running in the experimental Dashboard. The full Draft Sweeper UI is being ported.',
					'underway'
				) }
			</p>
			<a className="components-button is-primary" href="edit.php?post_status=draft&post_type=post">
				{ __( 'See drafts', 'underway' ) }
			</a>
		</div>
	);
}
