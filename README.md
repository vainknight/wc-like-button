"Like" button for WooCommerce products, with a persistent counter and full visual customization.

== Description ==

Adds an "I'm Interested" button to WooCommerce product pages. Each visitor can mark it only once per product (controlled via cookie), and the counter is stored persistently in the database (product post meta).

From Settings → I'm Interested you can customize without touching code:

* Button colors, typography, dimensions, and border (including hover and "already marked" states)
* Button icon (class, color, and size)
* Counter typography, colors, and dimensions (badge)
* Counter icon (class, color, and size)
* Text displayed for each button state

Usage: add the shortcode `[me_interesa_boton]`, or drag the "I'm Interested" widget in the Elementor editor (under "I'm Interested" or "WooCommerce" category) inside a product page.

Developed by [Fran Velazco](https://www.linkedin.com/in/fran-velazco/).
Built with Claude (Anthropic) as a development assistant.

== Installation ==

1. Upload the `me-interesa-boton` folder to `/wp-content/plugins/`.
2. Activate it through the 'Plugins' menu in WordPress (requires active WooCommerce).
3. Go to Settings → I'm Interested to customize the button appearance.
4. Place the shortcode `[me_interesa_boton]` wherever you want to display the button on a product page.

== Changelog ==

= 1.2.0 =
* Added the Elementor widget "I'm Interested" (was missing in the previous version, causing it not to show up in the widgets panel).

= 1.1.0 =
* Removed any brand references from the plugin.
* New settings page (Settings → I'm Interested) with full customization: colors, typography, dimensions, borders, and icons for both the button and counter.
* Dynamically generated CSS from saved options.

= 1.0.0 =
* Initial release: shortcode, cookie-based counter, separate CSS and JS files within the plugin.
