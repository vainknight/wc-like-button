<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CMI_Core
 * --------
 * Mismo comportamiento del shortcode original [me_interesa_boton]:
 * botón + contador guardado en post meta, control por cookie para
 * no duplicar el conteo por visitante, y AJAX con nonce.
 * El aspecto visual ahora se genera dinámicamente a partir de las
 * opciones guardadas en Ajustes → Me Interesa.
 */
class CMI_Core {

    private static $instancia = null;

    public static function instancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __construct() {
        add_shortcode('me_interesa_boton', array($this, 'shortcode_boton'));
        add_action('wp_ajax_cap_me_interesa', array($this, 'ajax_me_interesa'));
        add_action('wp_ajax_nopriv_cap_me_interesa', array($this, 'ajax_me_interesa'));
        add_action('wp_enqueue_scripts', array($this, 'cargar_assets'));
        add_action('wp_head', array($this, 'imprimir_css_dinamico'));
    }

    // ---------------------------------------------------------
    // Shortcode: [me_interesa_boton]
    // ---------------------------------------------------------
    public function shortcode_boton($atts) {
        if (!function_exists('wc_get_product')) {
            return '';
        }

        $product_id = get_the_ID();
        if (!$product_id) {
            return '';
        }

        $o = CMI_Opciones::instancia()->obtener_opciones();

        $contador    = (int) get_post_meta($product_id, '_cap_me_interesa_count', true);
        $cookie_name = 'cap_interes_' . $product_id;
        $ya_marco    = isset($_COOKIE[$cookie_name]);

        $texto_boton   = $ya_marco ? $o['texto_marcado'] : $o['texto_boton'];
        $icono_boton   = !empty($o['btn_icono_mostrar'])
            ? '<i class="' . esc_attr($o['btn_icono_clase']) . ' cmi-btn-icono" aria-hidden="true"></i>'
            : '';
        $icono_contador = !empty($o['contador_icono_mostrar'])
            ? '<i class="' . esc_attr($o['contador_icono_clase']) . ' cmi-contador-icono" aria-hidden="true"></i>'
            : '';

        ob_start();
        ?>
        <div class="cmi-wrapper">
            <button type="button"
                    class="cmi-btn <?php echo $ya_marco ? 'cmi-marcado' : ''; ?>"
                    data-product-id="<?php echo esc_attr($product_id); ?>"
                    data-nonce="<?php echo esc_attr(wp_create_nonce('cap_me_interesa_nonce')); ?>"
                    <?php echo $ya_marco ? 'disabled' : ''; ?>>
                <?php echo $icono_boton; ?>
                <span class="cmi-btn-texto"><?php echo esc_html($texto_boton); ?></span>
            </button>
            <span class="cmi-badge">
                <?php echo $icono_contador; ?>
                <span class="cmi-contador"><?php echo esc_html($contador); ?></span>
            </span>
        </div>
        <?php
        return ob_get_clean();
    }

    // ---------------------------------------------------------
    // AJAX: registra el "me interesa" una sola vez por visitante/producto
    // ---------------------------------------------------------
    public function ajax_me_interesa() {
        check_ajax_referer('cap_me_interesa_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error();
        }

        $cookie_name = 'cap_interes_' . $product_id;
        if (isset($_COOKIE[$cookie_name])) {
            $count = (int) get_post_meta($product_id, '_cap_me_interesa_count', true);
            wp_send_json_success(array('count' => $count));
        }

        $count = (int) get_post_meta($product_id, '_cap_me_interesa_count', true);
        $count++;
        update_post_meta($product_id, '_cap_me_interesa_count', $count);

        setcookie($cookie_name, '1', time() + YEAR_IN_SECONDS, '/');

        $o = CMI_Opciones::instancia()->obtener_opciones();
        wp_send_json_success(array(
            'count'  => $count,
            'texto'  => $o['texto_marcado'],
        ));
    }

    // ---------------------------------------------------------
    // Assets: Font Awesome + JS del click (el CSS es dinámico, ver abajo)
    // ---------------------------------------------------------
    public function cargar_assets() {
        wp_enqueue_style(
            'font-awesome-cmi',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
            array(),
            '6.5.1'
        );

        wp_enqueue_script(
            'cmi-me-interesa',
            CMI_PLUGIN_URL . 'assets/me-interesa.js',
            array(),
            CMI_VERSION,
            true
        );

        wp_localize_script('cmi-me-interesa', 'cmiAjax', array(
            'url' => admin_url('admin-ajax.php'),
        ));
    }

    // ---------------------------------------------------------
    // CSS dinámico — generado desde las opciones guardadas en el admin
    // ---------------------------------------------------------
    public function imprimir_css_dinamico() {
        $o = CMI_Opciones::instancia()->obtener_opciones();
        ?>
        <style id="cmi-estilos-dinamicos">
            .cmi-wrapper {
                display: flex;
                align-items: center;
                gap: 10px;
                margin: 15px 0;
            }
            .cmi-btn {
                display: flex;
                align-items: center;
                gap: 8px;
                background: <?php echo esc_html($o['btn_color_fondo']); ?>;
                color: <?php echo esc_html($o['btn_color_texto']); ?>;
                border: <?php echo esc_html($o['btn_ancho_borde']); ?>px solid <?php echo esc_html($o['btn_color_borde']); ?>;
                border-radius: <?php echo esc_html($o['btn_radio_borde']); ?>px;
                padding: <?php echo esc_html($o['btn_padding_v']); ?>px <?php echo esc_html($o['btn_padding_h']); ?>px;
                cursor: pointer;
                font-size: <?php echo esc_html($o['btn_tamano_fuente']); ?>px;
                font-family: <?php echo esc_html($o['btn_familia_fuente']); ?>;
                line-height: 1.2;
                transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            }
            .cmi-btn-icono {
                color: <?php echo esc_html($o['btn_icono_color']); ?>;
                font-size: <?php echo esc_html($o['btn_icono_tamano']); ?>px;
            }
            .cmi-btn:hover {
                background: <?php echo esc_html($o['btn_hover_color_fondo']); ?>;
                color: <?php echo esc_html($o['btn_hover_color_texto']); ?>;
                border-color: <?php echo esc_html($o['btn_hover_color_borde']); ?>;
            }
            .cmi-btn:hover .cmi-btn-texto {
                color: <?php echo esc_html($o['btn_hover_color_texto']); ?>;
            }
            .cmi-btn:disabled,
            .cmi-btn.cmi-marcado {
                opacity: 0.85;
                cursor: default;
                background: <?php echo esc_html($o['btn_marcado_color_fondo']); ?>;
                border-color: <?php echo esc_html($o['btn_marcado_color_borde']); ?>;
                color: <?php echo esc_html($o['btn_marcado_color_texto']); ?>;
            }
            .cmi-btn:disabled:hover,
            .cmi-btn.cmi-marcado:hover {
                background: <?php echo esc_html($o['btn_marcado_color_fondo']); ?>;
                color: <?php echo esc_html($o['btn_marcado_color_texto']); ?>;
                border-color: <?php echo esc_html($o['btn_marcado_color_borde']); ?>;
            }
            .cmi-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: <?php echo esc_html($o['contador_color_fondo']); ?>;
                color: <?php echo esc_html($o['contador_color_texto']); ?>;
                padding: <?php echo esc_html($o['contador_padding_v']); ?>px <?php echo esc_html($o['contador_padding_h']); ?>px;
                border-radius: <?php echo esc_html($o['contador_radio_borde']); ?>px;
                font-size: <?php echo esc_html($o['contador_tamano_fuente']); ?>px;
                font-family: <?php echo esc_html($o['contador_familia_fuente']); ?>;
                font-weight: <?php echo esc_html($o['contador_peso_fuente']); ?>;
            }
            .cmi-contador-icono {
                color: <?php echo esc_html($o['contador_icono_color']); ?>;
                font-size: <?php echo esc_html($o['contador_icono_tamano']); ?>px;
            }
        </style>
        <?php
    }
}
