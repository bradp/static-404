<?php
/**
 * Plugin Name: Static 404
 * Description: Quickly output a 404 for static files that aren't found, rather than loading the normal 404 page.
 * Version:     1.1.0
 * Author:      Brad Parbs
 * Author URI:  https://bradparbs.com/
 * License:     GPLv2
 * Text Domain: static-404
 * Domain Path: /lang/
 *
 * @package static-404
 */

namespace Static404;

defined( 'ABSPATH' ) || die();

/**
 *
 * If the plugin is installed as an mu-plugin, then we can hook in earlier,
 * otherwise we need to wait until plugins_loaded.
 */
add_action( get_early_action_to_use(), __NAMESPACE__ . '\\process_request' );

/**
 * If a request comes in for a static file and the webserver hasn't already
 * handled it, then we want to 404 as quickly as possible without loading WordPress.
 */
function process_request() {
	// If we don't have a request, then bail out.
	// We'll be parsing this request to determine
	// if a static file is being requested.
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	// Allow short-circuiting the logic.
	if ( should_process_request() ) {
		return;
	}

	// Bail out if we don't have an extension or if the extension isn't in the list. )
	$req_ext = get_request_extension();
	if ( ! $req_ext || ! in_array( $req_ext, get_extensions(), true ) ) {
		return;
	}

	// Set a 404.
	http_response_code( apply_filters( 'static_404_response_code', 404 ) );

	// Kill the request.
	// phpcs:ignore
	wp_die( get_message() );
}

/**
 * Determine the earliest `loaded` action we can use. If we're in an mu-plugin,
 * then fire it on mu-plugins_loaded, otherwise we need to wait until plugins_loaded.
 *
 * @return string Action to use.
 */
function get_early_action_to_use() {
	if ( ! did_action( 'muplugins_loaded' )  ) {
		return 'muplugins_loaded';
	} else {
		return 'plugins_loaded';
	}
}

/**
 * Get the extension of the request.
 *
 * @return string The extension of the request.
 */
function get_request_extension() {
	// Grab the file extension from our request.
	$request = wp_check_filetype( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

	// If we have a file extension, then return it.
	return isset( $request['ext'] ) ? $request['ext'] : '';
}

/**
 * Get the list of file extensions that should be checked.
 * filter `static_404_file_extensions` to modify.
 *
 * @return array Array of file extensions that could be static-404ed.
 */
function get_extensions() {
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
function get_message() {
	return apply_filters( 'static_404_message', '404 ' . get_status_header_desc( 404 ) );
}

/**
 * Allow filters to determine if we should process the request.
 *
 * @return bool Default is false unless filtered.
 */
function should_process_request() {
	return apply_filters( 'static_404_should_process_request', $_SERVER['REQUEST_URI'], false );
}
