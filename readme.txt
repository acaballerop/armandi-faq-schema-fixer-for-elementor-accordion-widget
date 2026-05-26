=== FAQ Schema Fixer for Elementor Accordion Widget ===
Contributors: acaballerop
Tags: elementor, faq schema, json-ld, schema.org, seo
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rebuild FAQPage JSON-LD for Elementor Nested Accordion widgets using the real questions and answers rendered on the page.

== Description ==

FAQ Schema Fixer for Elementor Accordion Widget is a focused compatibility plugin for sites that use Elementor's Nested Accordion widget as an FAQ block.

Some setups can end up outputting incomplete or incorrect FAQ schema in the final HTML. This plugin rebuilds the FAQPage JSON-LD on the server by reading the actual accordion question and answer content rendered on the page.

What it does:

* Lets you target specific WordPress pages only.
* Lets you target a specific Elementor Nested Accordion widget through a custom CSS class.
* Extracts the visible question and answer text from each accordion item.
* Rebuilds a valid `FAQPage` JSON-LD structure.
* Replaces an existing FAQ JSON-LD block inside the widget when one is already present.
* Adds a new FAQ JSON-LD script when needed.
* Ships translation-ready and includes a bundled Spanish (`es_ES`) translation for the admin UI.

This plugin is intentionally small and focused. It adds a lightweight settings page so you can decide exactly where the fix runs.

= Requirements =

* WordPress 6.2 or higher
* PHP 7.4 or higher
* Elementor
* Elementor Nested Accordion widgets used as FAQ content

= External services =

This plugin does not connect to any external service.

All processing happens on the same WordPress site where the plugin is installed.

= Notes =

* The plugin runs only on the frontend.
* It does not run in wp-admin, feeds, or JSON/REST requests.
* It is designed specifically for Elementor's `Nested Accordion` widget markup.
* The source language is English, and the plugin includes a bundled Spanish translation.

== Installation ==

1. Upload the `faq-schema-fixer-for-elementor-accordion-widget` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress admin.
2. Activate the plugin through the `Plugins` screen in WordPress.
3. Open `Tools > FAQ Schema Fixer`.
4. Enter a custom CSS class that you assign to the Elementor Nested Accordion widget, for example `faq-schema-fix`.
5. Search and select the pages where the fix should run.
6. Save the settings.
7. Make sure Elementor and the Nested Accordion widget are being used for FAQ sections on those pages.
8. Load one of the selected pages and inspect the final HTML or structured data output.
9. Validate the result with `https://search.google.com/test/rich-results` or `https://validator.schema.org/`.

The plugin does not run globally. It only runs on the selected pages and for the targeted accordion class.

== Frequently Asked Questions ==

= Does this plugin require Elementor? =

Yes. It is built specifically for Elementor Nested Accordion widgets.

= Does it work with Elementor Pro only? =

It depends on where the Nested Accordion widget is available in your Elementor setup. The plugin itself only looks at the final rendered markup.

= Does it add FAQ schema to all accordions automatically? =

No. It only processes Elementor Nested Accordion widgets that match the custom CSS class you configure in the plugin settings, and only on the selected pages.

= Does it change the visible frontend design? =

No. It only modifies the generated HTML output to correct or inject structured data.

= How do I use it with Elementor? =

1. Edit the page with Elementor.
2. Select the Nested Accordion widget that should behave as an FAQ block.
3. In `Advanced > CSS Classes`, add a unique class such as `faq-schema-fix`.
4. Save the page.
5. Open `Tools > FAQ Schema Fixer`, enter the same class, and select the pages where it should run.
6. Save the settings and test the final HTML output.

= Where does Elementor place the custom CSS class? =

Elementor adds the class to the outer widget wrapper in the frontend markup. For example:

`<div class="elementor-element ... faq-schema-fixer elementor-widget elementor-widget-n-accordion">`

That is the exact wrapper this plugin targets together with `elementor-widget-n-accordion`.

= Does it use external APIs or third-party services? =

No. Everything runs locally on your WordPress server.

== Screenshots ==

1. Elementor page using Nested Accordion as an FAQ block.
2. Plugin settings screen where the target pages and widget CSS class are configured.
3. Frontend output with corrected FAQPage JSON-LD in the final HTML.

== Upgrade Notice ==

= 1.2.0 =

Moves the plugin page to Tools, adds a direct settings link on the Plugins screen, adds validation links, and replaces the page multi-select with a searchable picker and selected pills.

= 1.1.0 =

Adds a settings page to target specific pages and a custom Elementor accordion CSS class.

= 1.0.0 =

Initial public release.

== Changelog ==

= 1.2.0 =

* Moved the plugin page from Settings to Tools.
* Added a direct Settings link on the Plugins screen.
* Replaced the page multi-select with a searchable page picker and selected pills.
* Added validation links for Google Rich Results Test and Schema.org Validator.
* Clarified that the configured CSS class is matched on Elementor's outer widget wrapper.
* Added bundled translation files and Spanish localization support for the admin UI.

= 1.1.0 =

* Added a settings page under WordPress Settings.
* Added page targeting so the fix runs only on selected pages.
* Added custom CSS class targeting so the fix runs only on the intended Elementor Nested Accordion widget.
* Added user guidance for configuring the plugin in Elementor.

= 1.0.0 =

* Initial public release.
* Frontend buffer-based FAQ schema correction for Elementor Nested Accordion widgets.
* Replacement or injection of `FAQPage` JSON-LD based on the real rendered questions and answers.
