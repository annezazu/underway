import { useEffect, useRef, useState } from 'react';
import { __ } from '@wordpress/i18n';

declare const wp: { apiFetch?: ( opts: { path: string } ) => Promise< { html?: string } > };

interface WidgetHtmlProps {
	slug: string;
	onAfterInject?: ( host: HTMLDivElement ) => void;
}

export default function WidgetHtml( { slug, onAfterInject }: WidgetHtmlProps ) {
	const hostRef = useRef< HTMLDivElement >( null );
	const [ status, setStatus ] = useState< 'loading' | 'ok' | 'error' >( 'loading' );

	useEffect( () => {
		let cancelled = false;
		if ( ! wp?.apiFetch ) {
			setStatus( 'error' );
			return;
		}
		wp.apiFetch( { path: `/underway/v1/widgets/${ slug }/html` } )
			.then( ( res ) => {
				if ( cancelled ) {
					return;
				}
				if ( hostRef.current && typeof res?.html === 'string' ) {
					hostRef.current.innerHTML = res.html;
					setStatus( 'ok' );
					onAfterInject?.( hostRef.current );
				} else {
					setStatus( 'error' );
				}
			} )
			.catch( () => {
				if ( ! cancelled ) {
					setStatus( 'error' );
				}
			} );
		return () => {
			cancelled = true;
		};
	}, [ slug ] );

	return (
		<>
			{ status === 'loading' && (
				<p style={ { margin: 0, color: '#50575e' } }>{ __( 'Loadingâ¦', 'underway' ) }</p>
			) }
			{ status === 'error' && (
				<p style={ { margin: 0, color: '#b32d2e' } }>
					{ __( 'Could not load this widget.', 'underway' ) }
				</p>
			) }
			<div ref={ hostRef } />
		</>
	);
}
