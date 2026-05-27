<?php
/**
 * Plugin Name:       Underway
 * Plugin URI:        https://github.com/annezazu/underway
 * Description:       A bundle of dashboard widgets that help your writing get underway: surface forgotten drafts, capture ideas, schedule future drafts, and build writing habits. Optional AI enhancements via the WordPress AI Client.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Anne McCarthy, Kelly Hoffman
 * Author URI:        https://github.com/annezazu
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       underway
 * Domain Path:       /languages
 *
 * @package Underway
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

if ( defined( 'UNDERWAY_VERSION' ) ) {
	return;
}

const UNDERWAY_VERSION  = '0.1.0';
const UNDERWAY_FILE     = __FILE__;
const UNDERWAY_DIR      = __DIR__;
define( 'UNDERWAY_URL', plugin_dir_url( __FILE__ ) );

spl_autoload_register( static function ( string $class ): void {
	$prefix = 'Underway\\';
	if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
		return;
	}
	$relative = substr( $class, strlen( $prefix ) );
	$file     = __DIR__ . '/src/' . str_replace( '\\', '/', $relative ) . '.php';
	if ( is_file( $file ) ) {
		require_once $file;
	}
} );

register_activation_hook( __FILE__, [ \Underway\Activation::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ \Underway\Activation::class, 'deactivate' ] );

add_action( 'plugins_loaded', [ \Underway\Plugin::class, 'boot' ], 5 );

// Experimental: register Underway widgets with the Gutenberg "Dashboard (Beta)"
// surface. The generated build manifest is only present when `wp-build` has
// been run, so the require is guarded.
if ( is_file( __DIR__ . '/build/build.php' ) ) {
	require_once __DIR__ . '/build/build.php';
	\Underway\Dashboard\ExperimentalDashboardWidgets::boot();
}
