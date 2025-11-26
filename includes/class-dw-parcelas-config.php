<?php
/**
 * Classe de configuração do plugin
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe DW_Parcelas_Config
 */
class DW_Parcelas_Config {

    /**
     * Configurações padrão do plugin
     *
     * @var array
     */
    private static $default_settings = array(
        'version' => DW_PARCELAS_VERSION,
        'auto_detect_pix' => true,
        'show_discount_percentage' => true,
        'enable_cart_notices' => true,
        'enable_product_display' => true,
        'css_animations' => true,
        'debug_mode' => false,
    );

    /**
     * Retorna as configurações do plugin
     *
     * @return array
     */
    public static function get_settings() {
        $settings = get_option('dw_parcelas_settings', array());
        return wp_parse_args($settings, self::$default_settings);
    }

    /**
     * Atualiza as configurações do plugin
     *
     * @param array $settings Novas configurações
     * @return bool
     */
    public static function update_settings($settings) {
        $sanitized_settings = self::sanitize_settings($settings);
        return update_option('dw_parcelas_settings', $sanitized_settings);
    }

    /**
     * Sanitiza as configurações
     *
     * @param array $settings Configurações a serem sanitizadas
     * @return array
     */
    private static function sanitize_settings($settings) {
        $sanitized = array();
        
        foreach (self::$default_settings as $key => $default_value) {
            if (isset($settings[$key])) {
                if (is_bool($default_value)) {
                    $sanitized[$key] = (bool) $settings[$key];
                } elseif (is_string($default_value)) {
                    $sanitized[$key] = sanitize_text_field($settings[$key]);
                } else {
                    $sanitized[$key] = $settings[$key];
                }
            } else {
                $sanitized[$key] = $default_value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Retorna uma configuração específica
     *
     * @param string $key Chave da configuração
     * @param mixed $default Valor padrão
     * @return mixed
     */
    public static function get_setting($key, $default = null) {
        $settings = self::get_settings();
        
        if (isset($settings[$key])) {
            return $settings[$key];
        }
        
        return $default;
    }

    /**
     * Verifica se o modo debug está ativo
     *
     * @return bool
     */
    public static function is_debug_mode() {
        return self::get_setting('debug_mode', false) || (defined('WP_DEBUG') && WP_DEBUG);
    }

    /**
     * Log de debug
     *
     * @param mixed $message Mensagem a ser logada
     * @param string $level Nível do log
     */
    public static function log($message, $level = 'info') {
        if (!self::is_debug_mode()) {
            return;
        }
        
        $log_message = is_array($message) || is_object($message) 
            ? print_r($message, true) 
            : $message;
            
        error_log(sprintf('[DW Parcelas Debug] %s: %s', strtoupper($level), $log_message));
    }

    /**
     * Retorna a versão do plugin
     *
     * @return string
     */
    public static function get_version() {
        return self::get_setting('version', DW_PARCELAS_VERSION);
    }

    /**
     * Verifica se a detecção automática de PIX está ativa
     *
     * @return bool
     */
    public static function is_auto_detect_pix_enabled() {
        return self::get_setting('auto_detect_pix', true);
    }

    /**
     * Verifica se deve exibir percentual de desconto
     *
     * @return bool
     */
    public static function should_show_discount_percentage() {
        return self::get_setting('show_discount_percentage', true);
    }

    /**
     * Verifica se deve exibir avisos no carrinho
     *
     * @return bool
     */
    public static function should_show_cart_notices() {
        return self::get_setting('enable_cart_notices', true);
    }

    /**
     * Verifica se deve exibir preço PIX na página do produto
     *
     * @return bool
     */
    public static function should_show_product_display() {
        return self::get_setting('enable_product_display', true);
    }

    /**
     * Verifica se animações CSS estão habilitadas
     *
     * @return bool
     */
    public static function are_css_animations_enabled() {
        return self::get_setting('css_animations', true);
    }

    /**
     * Reseta as configurações para os valores padrão
     *
     * @return bool
     */
    public static function reset_settings() {
        return update_option('dw_parcelas_settings', self::$default_settings);
    }

    /**
     * Remove todas as configurações do plugin
     *
     * @return bool
     */
    public static function delete_settings() {
        return delete_option('dw_parcelas_settings');
    }
}

