import WidgetHtml from '../_shared/widget-html';

declare global {
	interface Window {
		UnderwayFutureDrafts?: { mount?: ( node: HTMLElement ) => void };
	}
}

export default function FutureDraftsWidget() {
	return (
		<WidgetHtml
			slug="future-drafts"
			onAfterInject={ ( host ) => {
				const root = host.querySelector< HTMLElement >( '#future-drafts-root' );
				if ( root && window.UnderwayFutureDrafts?.mount ) {
					window.UnderwayFutureDrafts.mount( root );
				}
			} }
		/>
	);
}
