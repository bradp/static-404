<?php
/**
 * Plugin Name: Static 404
 * Description: Quickly output a 404 for static files that aren't found, rather than loading the normal 404 page.
 * Version:     1.0.3
 * Author:      Brad Parbs
 * Author URI:  https://bradparbs.com/
 * License:     GPLv2
 * Text Domain: static-404
 * Domain Path: /lang/
 *
 * @package static-404
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If the plugin is installed as an mu-plugin, then we can hook in earlier,
// otherwise we need to wait until plugins_loaded.
$static_404_hook = ! did_action( 'muplugins_loaded' ) ? 'muplugins_loaded' : 'plugins_loaded';

/**
 * If a request comes in for a static file and the webserver hasn't already handled it,
 * then we want to 404 as quickly as possible without loading WordPress.
 */
add_action(
	$static_404_hook,
	function () {
		// If we don't have a request, then bail out.
		// We'll be parsing this request to determine
		// if a static file is being requested.
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		// Grab the file extension from our request.
		$request = wp_check_filetype( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
		$req_ext = isset( $request['ext'] ) ? $request['ext'] : '';

		// Bail out if we don't have an extension or if the extension isn't in the list.
		if ( ! $req_ext || ! in_array( $req_ext, static_404_get_extensions(), true ) ) {
			return;
		}

		// Set a 404.
		http_response_code( 404 );

		// Kill the request.
		// phpcs:ignore
		wp_die( static_404_get_message() );
	}
);

/**
 * Get the list of file extensions that should be checked.
 * filter `static_404_file_extensions` to modify.
 *
 * @return array Array of file extensions that could be static-404ed.
 */
function static_404_get_extensions() {

	$wp_ext_types = wp_get_ext_types();
	$extensions   = [];

	// Flatten the array from [ 'image', 'audio', ... ] to one
	// array with all the of the extensions.
	foreach ( array_keys( $wp_ext_types ) as $ext_type ) {
		$extensions = array_merge( $extensions, $wp_ext_types[ $ext_type ] );
	}

	// Unset commonly used extensions that a user might have in a url for normal pages.
	unset( $extensions['html'] );
	unset( $extensions['htm'] );
	unset( $extensions['php'] );

	// Add / remove extensions if you'd like!
	return apply_filters( 'static_404_extensions', $extensions );
}

/**
 * Get the error message. Default is '404 - Not Found'.
 *
 * @return string 404 error message.
 */
function static_404_get_message() {
	return apply_filters( 'static_404_message', '404 ' . get_status_header_desc( 404 ) );
}
