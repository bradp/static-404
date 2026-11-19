<?php
/**
 * Plugin Name:       Static 404
 * Description:       Quickly output a 404 for static files that aren't found, rather than loading the normal 404 page.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Brad Parbs
 * Author URI:        https://bradparbs.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       static-404
 *
 * @package static-404
 */

namespace Static404;

defined( 'ABSPATH' ) || exit;

/*
 * If the plugin is installed as an mu-plugin, then we can hook in earlier,
 * otherwise we need to wait until plugins_loaded.
 */
add_action( get_early_action_to_use(), __NAMESPACE__ . '\\process_request' );

/**
 * If a request comes in for a static file and the webserver hasn't already
 * handled it, then we want to 404 as quickly as possible without loading WordPress.
 */
function process_request() {
	// Only handle front-end requests that the webserver routed to WordPress' index.php.
	if ( ! is_front_end_request() ) {
		return;
	}

	$request_uri = get_request_uri();
	if ( '' === $request_uri ) {
		return;
	}

	// Allow short-circuiting the logic.
	if ( should_skip_request( $request_uri ) ) {
		return;
	}

	// Bail out if we don't have an extension or if the extension isn't in the list.
	$req_ext = get_request_extension( $request_uri );
	if ( '' === $req_ext || ! in_array( $req_ext, get_extensions(), true ) ) {
		return;
	}

	if ( ! headers_sent() ) {
		status_header( (int) apply_filters( 'static_404_response_code', 404 ) );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
	}

	echo wp_kses_post( get_message() );
	exit;
}

/**
 * Determine the earliest `loaded` action we can use. If we're in an mu-plugin,
 * then fire it on muplugins_loaded, otherwise we need to wait until plugins_loaded.
 *
 * @return string Action to use.
 */
function get_early_action_to_use() {
	if ( ! did_action( 'muplugins_loaded' ) ) {
		return 'muplugins_loaded';
	}

	return 'plugins_loaded';
}

/**
 * Whether this is a front-end request going through index.php. Requests for
 * wp-login.php, wp-cron.php, admin-ajax.php, the admin, etc. are left alone.
 *
 * @return bool True if this is a front-end request.
 */
function is_front_end_request() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
		return false;
	}

	$script = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : '';

	return 'index.php' === basename( $script );
}

/**
 * Get the current request URI.
 *
 * @return string Request URI, or an empty string if there isn't one.
 */
function get_request_uri() {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return '';
	}

	return esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
}

/**
 * Get the extension of the request.
 *
 * @param string $request_uri Request URI.
 *
 * @return string The lowercased extension of the request, or an empty string.
 */
function get_request_extension( $request_uri ) {
	$path = wp_parse_url( $request_uri, PHP_URL_PATH );

	if ( ! is_string( $path ) || '' === $path ) {
		return '';
	}

	return strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
}

/**
 * Get the list of file extensions that should be checked.
 * Filter `static_404_extensions` to modify.
 *
 * @return string[] Array of file extensions that could be static-404ed.
 */
function get_extensions() {
	// Flatten [ 'image' => [...], 'audio' => [...], ... ] into one list of extensions.
	$extensions = array_merge( ...array_values( wp_get_ext_types() ) );

	// Remove extensions that WordPress or other plugins commonly serve dynamically,
	// e.g. robots.txt, ads.txt, llms.txt, or pretty permalinks ending in .html.
	$extensions = array_diff( $extensions, [ 'html', 'htm', 'php', 'txt' ] );

	// Add / remove extensions if you'd like!
	return array_values( (array) apply_filters( 'static_404_extensions', array_values( $extensions ) ) );
}

/**
 * Get the error message. Default is '404 Not Found'.
 *
 * @return string 404 error message.
 */
function get_message() {
	return (string) apply_filters( 'static_404_message', '404 ' . get_status_header_desc( 404 ) );
}

/**
 * Allow filters to skip processing the request.
 *
 * @param string $request_uri The current request URI.
 *
 * @return bool Default is false unless filtered.
 */
function should_skip_request( $request_uri ) {
	return (bool) apply_filters( 'static_404_should_process_request', false, $request_uri );
}
