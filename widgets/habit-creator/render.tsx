import { __ } from '@wordpress/i18n';
import './render.scss';

interface Props {
	attributes?: Record< string, unknown >;
}

export default function HabitCreatorWidget( _props: Props ) {
	return (
		<div className="underway-widget">
			<p className="underway-widget__lede">
				{ __(
					'Spot patterns in what you write and lean into a writing rhythm.',
					'underway'
				) }
			</p>
			<p className="underway-widget__hint">
				{ __(
					'Demo placeholder running in the experimental Dashboard. The full Habit Creator UI is being ported.',
					'underway'
				) }
			</p>
			<a className="components-button is-primary" href="post-new.php">
				{ __( 'Start a new post', 'underway' ) }
			</a>
		</div>
	);
}
