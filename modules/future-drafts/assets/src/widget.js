import { createRoot } from '@wordpress/element';
import App from './App';
import './widget.scss';

function mount( node ) {
	if ( ! node ) {
		return;
	}
	createRoot( node ).render( <App /> );
}

// Expose a programmatic mount so consumers (e.g. the experimental Dashboard
// surface, which injects the root div after DOMContentLoaded) can hydrate
// on demand.
if ( typeof window !== 'undefined' ) {
	window.UnderwayFutureDrafts = window.UnderwayFutureDrafts || {};
	window.UnderwayFutureDrafts.mount = mount;
}

// Classic dashboard auto-mount: the root div is in the DOM by the time
// this script runs.
const root = document.getElementById( 'future-drafts-root' );
if ( root ) {
	mount( root );
}
