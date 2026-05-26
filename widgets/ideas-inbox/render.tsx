import { __ } from '@wordpress/i18n';
import './render.scss';

interface Props {
	attributes?: Record< string, unknown >;
}

export default function IdeasInboxWidget( _props: Props ) {
	return (
		<div className="underway-widget">
			<p className="underway-widget__lede">
				{ __( 'Drop ideas for your future self to blog about.', 'underway' ) }
			</p>
			<p className="underway-widget__hint">
				{ __(
					'Demo placeholder running in the experimental Dashboard. The full Ideas Inbox UI is being ported.',
					'underway'
				) }
			</p>
			<a className="components-button is-primary" href="edit.php?page=ideas-inbox">
				{ __( 'Open Ideas Inbox', 'underway' ) }
			</a>
		</div>
	);
}
