<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CMI_Elementor
 * -------------
 * Registra el widget "Me Interesa" para Elementor, solo si Elementor
 * está activo. No genera ningún error si no lo está.
 */
class CMI_Elementor {

    private static $instancia = null;

    public static function instancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __construct() {
        add_action('elementor/widgets/register', array($this, 'registrar_widget'));
        add_action('elementor/elements/categories_registered', array($this, 'registrar_categoria'));
    }

    public function registrar_categoria($elements_manager) {
        $elements_manager->add_category('me-interesa', array(
            'title' => __('Me Interesa', 'me-interesa-boton'),
            'icon'  => 'fa fa-heart',
        ));
    }

    public function registrar_widget($widgets_manager) {
        if (!did_action('elementor/loaded')) {
            return;
        }

        require_once CMI_PLUGIN_DIR . 'includes/class-cmi-elementor-widget.php';
        $widgets_manager->register(new \CMI_Elementor_Widget());
    }
}
