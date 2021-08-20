=== Static 404 ===
Contributors: bradparbs, surfboards
Tags: performance, 404, errors
Requires at least: 5.2
Tested up to: 5.8
Stable tag: 1.1.0
License: GPLv2 or later
Requires PHP: 5.6

A WordPress plugin to quickly send a 404 for missing static files.

== Description ==

Quickly output a 404 for static files that aren't found, rather than loading the normal 404 page.

Any static files ( images, text, pdfs, etc ) that don't exist will 404 as soon as possible, rather than loading the entire WordPress application.

## Details

By default, the list of extensions to check are the results of `wp_get_ext_types`, but can be filtered with `static_404_extensions`.

The output is a static page with the text `404 Not Found`, this text can be edited by filtering `static_404_message`.

A 404 status code will be used, but can be filtered with `static_404_response_code`.

Passing true to `static_404_should_process_request` will short-circuit and skip processing the request. This filter gets passed the current request.


== Installation ==

 - Install the plugin.
 - Magically have faster and less expensive 404s!


== Changelog ==

= 1.1.00 =
 * Added more filters

= 1.0.3 =
 * Initial release
