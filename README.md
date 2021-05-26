# Static 404

> A WordPress plugin to quickly send a 404 for missing static files.

Quickly output a 404 for static files that aren't found, rather than loading the normal 404 page.

Any static files ( images, text, pdfs, etc ) that don't exist will 404 as soon as possible, rather than loading the entire WordPress application.


## Details

By default, the list of extensions to check are the results of `wp_get_ext_types`, but can be filtered with `static_404_extensions`.

The output is a static page with the text `404 Not Found`, this text can be edited by filtering `static_404_message`.
