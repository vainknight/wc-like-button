<?php
if (!defined('ABSPATH')) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class CMI_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'cmi-me-interesa';
    }

    public function get_title() {
        return __('Me Interesa', 'me-interesa-boton');
    }

    public function get_icon() {
        return 'eicon-heart-o';
    }

    public function get_categories() {
        return array('me-interesa', 'woocommerce-elements', 'general');
    }

    public function get_keywords() {
        return array('woocommerce', 'producto', 'interes', 'boton', 'like');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'seccion_info',
            array(
                'label' => __('Información', 'me-interesa-boton'),
            )
        );

        $this->add_control(
            'nota_ajustes',
            array(
                'type' => Controls_Manager::RAW_HTML,
                'raw'  => sprintf(
                    /* translators: %s: URL de la página de ajustes */
                    __('Este widget usa el shortcode [me_interesa_boton]. Solo se muestra dentro de una página de producto de WooCommerce. Personaliza colores, tipografía e iconos desde <a href="%s" target="_blank">Ajustes → Me Interesa</a>.', 'me-interesa-boton'),
                    esc_url(admin_url('options-general.php?page=cmi-ajustes'))
                ),
                'content_classes' => 'elementor-descriptor',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        if (!function_exists('wc_get_product') || !is_singular('product')) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div style="padding:16px;border:1px dashed #999;text-align:center;color:#666;">';
                echo esc_html__('Vista previa: el botón "Me interesa" se mostrará aquí dentro de una página de producto de WooCommerce.', 'me-interesa-boton');
                echo '</div>';
            }
            return;
        }

        echo do_shortcode('[me_interesa_boton]');
    }

    protected function content_template() {
        ?>
        <div style="padding:16px;border:1px dashed #999;text-align:center;color:#666;">
            <?php esc_html_e('Vista previa: el botón "Me interesa" se mostrará aquí dentro de una página de producto de WooCommerce.', 'me-interesa-boton'); ?>
        </div>
        <?php
    }
}
