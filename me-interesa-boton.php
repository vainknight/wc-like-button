<?php
/**
 * Plugin Name: "Like" Button (WooCommerce)
 * Description: "Like" button for WooCommerce products, featuring a persistent counter, cookie-based voting control to prevent duplicate votes per visitor, and full visual customization options.
 * Version: 1.2.0
 * Author: Fran Velazco
 * Author URI: https://www.linkedin.com/in/fran-velazco/
 * Text Domain: me-interesa-boton
 * Requires Plugins: woocommerce
 * License: GPLv2
 */

if (!defined('ABSPATH')) {
    exit; // Direct access not allowed.
}

define('CMI_PLUGIN_FILE', __FILE__);
define('CMI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CMI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CMI_VERSION', '1.2.0');

require_once CMI_PLUGIN_DIR . 'includes/class-cmi-opciones.php';
require_once CMI_PLUGIN_DIR . 'includes/class-cmi-core.php';
require_once CMI_PLUGIN_DIR . 'includes/class-cmi-elementor.php';

// Notice if WooCommerce is not active — the plugin won't break the site,
// it simply won't display anything, but we notify in admin.
add_action('admin_notices', function () {
    if (!function_exists('wc_get_product') && current_user_can('activate_plugins')) {
        echo '<div class="notice notice-warning"><p>';
        echo esc_html__('The "I\'m Interested Button" plugin requires WooCommerce to be active to work.', 'me-interesa-boton');
        echo '</p></div>';
    }
});

add_action('plugins_loaded', function () {
    CMI_Opciones::instancia();
    CMI_Core::instancia();
    CMI_Elementor::instancia();
});
