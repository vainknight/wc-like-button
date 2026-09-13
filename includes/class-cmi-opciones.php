<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CMI_Opciones
 * ------------
 * Guarda y expone todas las opciones de personalización visual del
 * botón "Me interesa" y su contador, y registra la página de ajustes
 * en el admin (Ajustes → Me Interesa).
 */
class CMI_Opciones {

    private static $instancia = null;
    const OPTION_KEY = 'cmi_opciones';

    public static function instancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'registrar_pagina'));
        add_action('admin_init', array($this, 'registrar_ajustes'));
    }

    /**
     * Valores por defecto — son exactamente los colores/medidas
     * originales del botón, para que al activar el plugin nada
     * cambie visualmente hasta que el usuario edite algo.
     */
    public static function defaults() {
        return array(
            // Botón
            'btn_color_fondo'         => '#0B2647',
            'btn_color_texto'         => '#ffffff',
            'btn_color_borde'         => '#0B2647',
            'btn_ancho_borde'         => 1,
            'btn_radio_borde'         => 4,
            'btn_padding_v'           => 10,
            'btn_padding_h'           => 16,
            'btn_tamano_fuente'       => 14,
            'btn_familia_fuente'      => 'inherit',
            // Estado hover
            'btn_hover_color_fondo'   => '#ffffff',
            'btn_hover_color_texto'   => '#0B2647',
            'btn_hover_color_borde'   => '#D4A72C',
            // Estado marcado/disabled
            'btn_marcado_color_fondo' => '#16305c',
            'btn_marcado_color_texto' => '#ffffff',
            'btn_marcado_color_borde' => '#16305c',
            // Icono del botón
            'btn_icono_mostrar'       => 1,
            'btn_icono_clase'         => 'fa-solid fa-heart',
            'btn_icono_color'         => '#ff5c5c',
            'btn_icono_tamano'        => 16,
            // Textos
            'texto_boton'             => 'Me interesa',
            'texto_marcado'           => '¡Gracias por tu interés!',
            // Badge / contador
            'contador_color_fondo'    => '#f5f0e4',
            'contador_color_texto'    => '#0B2647',
            'contador_tamano_fuente'  => 14,
            'contador_familia_fuente' => 'inherit',
            'contador_peso_fuente'    => 600,
            'contador_radio_borde'    => 999,
            'contador_padding_v'      => 6,
            'contador_padding_h'      => 12,
            // Icono del contador
            'contador_icono_mostrar'  => 1,
            'contador_icono_clase'    => 'fa-solid fa-heart',
            'contador_icono_color'    => '#ff5c5c',
            'contador_icono_tamano'   => 13,
        );
    }

    public function obtener_opciones() {
        $guardadas = get_option(self::OPTION_KEY, array());
        return wp_parse_args($guardadas, self::defaults());
    }

    // ---------------------------------------------------------
    // Registro en Ajustes → Me Interesa
    // ---------------------------------------------------------
    public function registrar_pagina() {
        add_options_page(
            __('Botón Me Interesa', 'me-interesa-boton'),
            __('Me Interesa', 'me-interesa-boton'),
            'manage_options',
            'cmi-ajustes',
            array($this, 'render_pagina')
        );
    }

    public function registrar_ajustes() {
        register_setting('cmi_grupo_opciones', self::OPTION_KEY, array(
            'sanitize_callback' => array($this, 'sanitizar_opciones'),
        ));
    }

    public function sanitizar_opciones($input) {
        $defaults = self::defaults();
        $limpio   = array();

        foreach ($defaults as $clave => $valor_defecto) {
            if (!isset($input[$clave])) {
                $limpio[$clave] = is_numeric($valor_defecto) ? 0 : '';
                continue;
            }

            $valor = $input[$clave];

            if (strpos($clave, 'color') !== false) {
                $limpio[$clave] = sanitize_hex_color($valor) ? $valor : $valor_defecto;
            } elseif (in_array($clave, array('btn_icono_mostrar', 'contador_icono_mostrar'), true)) {
                $limpio[$clave] = !empty($valor) ? 1 : 0;
            } elseif (in_array($clave, array('btn_icono_clase', 'contador_icono_clase'), true)) {
                // Clases de ícono tipo Font Awesome: solo letras, números, espacios y guiones.
                $limpio[$clave] = preg_replace('/[^a-zA-Z0-9\-\s]/', '', $valor);
            } elseif (in_array($clave, array('texto_boton', 'texto_marcado', 'btn_familia_fuente', 'contador_familia_fuente'), true)) {
                $limpio[$clave] = sanitize_text_field($valor);
            } else {
                $limpio[$clave] = is_numeric($valor) ? floatval($valor) : $valor_defecto;
            }
        }

        return $limpio;
    }

    public function render_pagina() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $o = $this->obtener_opciones();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Botón Me Interesa — Ajustes', 'me-interesa-boton'); ?></h1>
            <p><?php esc_html_e('Personaliza colores, tipografía, dimensiones e iconos del botón y del contador. Usa el shortcode [me_interesa_boton] en la página de producto.', 'me-interesa-boton'); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields('cmi_grupo_opciones'); ?>

                <h2 class="title"><?php esc_html_e('Botón', 'me-interesa-boton'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->fila_color('btn_color_fondo', __('Color de fondo', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_color('btn_color_texto', __('Color del texto', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_color('btn_color_borde', __('Color del borde', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_numero('btn_ancho_borde', __('Ancho del borde (px)', 'me-interesa-boton'), $o, 0, 20); ?>
                    <?php $this->fila_numero('btn_radio_borde', __('Radio del borde (px)', 'me-interesa-boton'), $o, 0, 999); ?>
                    <?php $this->fila_numero('btn_padding_v', __('Relleno vertical (px)', 'me-interesa-boton'), $o, 0, 100); ?>
                    <?php $this->fila_numero('btn_padding_h', __('Relleno horizontal (px)', 'me-interesa-boton'), $o, 0, 100); ?>
                    <?php $this->fila_numero('btn_tamano_fuente', __('Tamaño de fuente del texto (px)', 'me-interesa-boton'), $o, 8, 72); ?>
                    <?php $this->fila_texto('btn_familia_fuente', __('Familia tipográfica (CSS font-family)', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_texto('texto_boton', __('Texto del botón (estado normal)', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_texto('texto_marcado', __('Texto del botón (ya marcado)', 'me-interesa-boton'), $o); ?>
                </table>

                <h2 class="title"><?php esc_html_e('Botón — estado al pasar el mouse (hover)', 'me-interesa-boton'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->fila_color('btn_hover_color_fondo', __('Color de fondo (hover)', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_color('btn_hover_color_texto', __('Color del texto (hover)', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_color('btn_hover_color_borde', __('Color del borde (hover)', 'me-interesa-boton'), $o); ?>
                </table>

                <h2 class="title"><?php esc_html_e('Botón — estado ya marcado', 'me-interesa-boton'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->fila_color('btn_marcado_color_fondo', __('Color de fondo (marcado)', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_color('btn_marcado_color_texto', __('Color del texto (marcado)', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_color('btn_marcado_color_borde', __('Color del borde (marcado)', 'me-interesa-boton'), $o); ?>
                </table>

                <h2 class="title"><?php esc_html_e('Icono del botón', 'me-interesa-boton'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->fila_checkbox('btn_icono_mostrar', __('Mostrar icono en el botón', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_texto('btn_icono_clase', __('Clase del icono (Font Awesome)', 'me-interesa-boton'), $o, 'Ej: fa-solid fa-heart'); ?>
                    <?php $this->fila_color('btn_icono_color', __('Color del icono', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_numero('btn_icono_tamano', __('Tamaño del icono (px)', 'me-interesa-boton'), $o, 8, 72); ?>
                </table>

                <h2 class="title"><?php esc_html_e('Contador (badge)', 'me-interesa-boton'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->fila_color('contador_color_fondo', __('Color de fondo', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_color('contador_color_texto', __('Color del texto', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_numero('contador_tamano_fuente', __('Tamaño de fuente (px)', 'me-interesa-boton'), $o, 8, 72); ?>
                    <?php $this->fila_texto('contador_familia_fuente', __('Familia tipográfica (CSS font-family)', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_numero('contador_peso_fuente', __('Peso de fuente (100–900)', 'me-interesa-boton'), $o, 100, 900); ?>
                    <?php $this->fila_numero('contador_radio_borde', __('Radio del borde (px)', 'me-interesa-boton'), $o, 0, 999); ?>
                    <?php $this->fila_numero('contador_padding_v', __('Relleno vertical (px)', 'me-interesa-boton'), $o, 0, 100); ?>
                    <?php $this->fila_numero('contador_padding_h', __('Relleno horizontal (px)', 'me-interesa-boton'), $o, 0, 100); ?>
                </table>

                <h2 class="title"><?php esc_html_e('Icono del contador', 'me-interesa-boton'); ?></h2>
                <table class="form-table" role="presentation">
                    <?php $this->fila_checkbox('contador_icono_mostrar', __('Mostrar icono en el contador', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_texto('contador_icono_clase', __('Clase del icono (Font Awesome)', 'me-interesa-boton'), $o, 'Ej: fa-solid fa-heart'); ?>
                    <?php $this->fila_color('contador_icono_color', __('Color del icono', 'me-interesa-boton'), $o); ?>
                    <?php $this->fila_numero('contador_icono_tamano', __('Tamaño del icono (px)', 'me-interesa-boton'), $o, 8, 72); ?>
                </table>

                <?php submit_button(__('Guardar cambios', 'me-interesa-boton')); ?>
            </form>
        </div>
        <?php
    }

    private function nombre_campo($clave) {
        return self::OPTION_KEY . '[' . $clave . ']';
    }

    private function fila_color($clave, $label, $o) {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($clave); ?>"><?php echo esc_html($label); ?></label></th>
            <td><input type="color" id="<?php echo esc_attr($clave); ?>" name="<?php echo esc_attr($this->nombre_campo($clave)); ?>" value="<?php echo esc_attr($o[$clave]); ?>"></td>
        </tr>
        <?php
    }

    private function fila_numero($clave, $label, $o, $min = 0, $max = 999) {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($clave); ?>"><?php echo esc_html($label); ?></label></th>
            <td><input type="number" min="<?php echo esc_attr($min); ?>" max="<?php echo esc_attr($max); ?>" id="<?php echo esc_attr($clave); ?>" name="<?php echo esc_attr($this->nombre_campo($clave)); ?>" value="<?php echo esc_attr($o[$clave]); ?>" class="small-text"></td>
        </tr>
        <?php
    }

    private function fila_texto($clave, $label, $o, $placeholder = '') {
        ?>
        <tr>
            <th scope="row"><label for="<?php echo esc_attr($clave); ?>"><?php echo esc_html($label); ?></label></th>
            <td><input type="text" id="<?php echo esc_attr($clave); ?>" name="<?php echo esc_attr($this->nombre_campo($clave)); ?>" value="<?php echo esc_attr($o[$clave]); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" class="regular-text"></td>
        </tr>
        <?php
    }

    private function fila_checkbox($clave, $label, $o) {
        ?>
        <tr>
            <th scope="row"><?php echo esc_html($label); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($this->nombre_campo($clave)); ?>" value="1" <?php checked(!empty($o[$clave])); ?>>
                    <?php esc_html_e('Activado', 'me-interesa-boton'); ?>
                </label>
            </td>
        </tr>
        <?php
    }
}
