import { __ } from '@wordpress/i18n';
import './render.scss';

interface Props {
	attributes?: Record< string, unknown >;
}

export default function FutureDraftsWidget( _props: Props ) {
	return (
		<div className="underway-widget">
			<p className="underway-widget__lede">
				{ __(
					"Create a draft for your future self. We'll bring it back when you're ready.",
					'underway'
				) }
			</p>
			<p className="underway-widget__hint">
				{ __(
					'Demo placeholder running in the experimental Dashboard. The full Future Drafts editor is being ported.',
					'underway'
				) }
			</p>
			<a className="components-button is-primary" href="edit.php?post_status=future&post_type=post">
				{ __( 'See scheduled posts', 'underway' ) }
			</a>
		</div>
	);
}
