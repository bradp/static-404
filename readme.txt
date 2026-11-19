=== Static 404 ===
Contributors: bradparbs, surfboards
Tags: performance, 404, errors
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to quickly send a 404 for missing static files.

== Description ==

Quickly output a 404 for static files that aren't found, rather than loading the normal 404 page.

Any static files ( images, text, pdfs, etc ) that don't exist will 404 as soon as possible, rather than loading the entire WordPress application.

== Available filters ==

By default, the list of extensions to check are the results of `wp_get_ext_types`, minus `html`, `htm`, `php`, and `txt`. This can be filtered with `static_404_extensions`.

The output is a static page with the text `404 Not Found`, this text can be edited by filtering `static_404_message`.

A 404 status code will be used, but can be filtered with `static_404_response_code`.

Returning true from `static_404_should_process_request` will short-circuit and skip processing the request. This filter is passed `false` and the current request URI.

Only front-end requests routed through `index.php` are checked. Requests for `wp-login.php`, `wp-cron.php`, `admin-ajax.php`, and the admin are never affected.

== Installation ==

 - Install the plugin.
 - Magically have faster and less expensive 404s!

== Changelog ==

= 1.2.0 =

* Fixed the plugin not doing anything since 1.1.0.
* Only front-end requests are handled. `wp-login.php`, `wp-cron.php`, `admin-ajax.php`, and other `.php` files are never blocked.
* `.html`, `.htm`, `.php`, and `.txt` URLs are never blocked, so dynamic files like `robots.txt` keep working.
* Responses now have a 404 status code. Previously they had a 500.
* `static_404_should_process_request` is now passed `false` and the request URI.
* Requires WordPress 6.0 and PHP 7.4, tested up to WordPress 7.1.

= 1.1.0 =
 * Added more filters

= 1.0.3 =
 * Initial release
