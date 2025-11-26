<?php
/**
 * Classe para funcionalidades de segurança
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe DW_Pix_Security
 */
class DW_Pix_Security {

    /**
     * Construtor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa os hooks de segurança
     */
    private function init_hooks() {
        // Adiciona nonce para formulários
        add_action('woocommerce_product_options_pricing', array($this, 'add_nonce_field'));
        
        // Verifica nonce ao salvar
        add_action('woocommerce_process_product_meta', array($this, 'verify_nonce'), 5);
        
        // Sanitiza dados de entrada
        add_filter('woocommerce_process_product_meta', array($this, 'sanitize_product_data'), 10, 1);
    }

    /**
     * Adiciona campo nonce para segurança
     */
    public function add_nonce_field() {
        wp_nonce_field('dw_pix_save_product', 'dw_pix_nonce');
    }

    /**
     * Verifica nonce antes de salvar
     *
     * @param int $post_id ID do post
     */
    public function verify_nonce($post_id) {
        // Verifica se é uma requisição válida
        if (!isset($_POST['dw_pix_nonce']) || !wp_verify_nonce($_POST['dw_pix_nonce'], 'dw_pix_save_product')) {
            return;
        }

        // Verifica permissões
        if (!current_user_can('edit_post', $post_id)) {
            wp_die(__('Você não tem permissão para editar este produto.', 'dw-price-to-pix'));
        }
    }

    /**
     * Sanitiza dados do produto
     *
     * @param int $post_id ID do post
     */
    public function sanitize_product_data($post_id) {
        if (isset($_POST['_pix_price'])) {
            $_POST['_pix_price'] = $this->sanitize_price($_POST['_pix_price']);
        }
    }

    /**
     * Sanitiza valor de preço
     *
     * @param mixed $price Preço a ser sanitizado
     * @return float
     */
    public function sanitize_price($price) {
        // Remove caracteres não numéricos exceto vírgula e ponto
        $price = preg_replace('/[^0-9,.]/', '', $price);
        
        // Converte vírgula para ponto
        $price = str_replace(',', '.', $price);
        
        // Converte para float
        $price = floatval($price);
        
        // Garante que não seja negativo
        return max(0, $price);
    }

    /**
     * Valida se um valor é um preço válido
     *
     * @param mixed $price Preço a ser validado
     * @return bool
     */
    public function is_valid_price($price) {
        if (!is_numeric($price)) {
            return false;
        }
        
        $price = floatval($price);
        
        return $price >= 0 && $price <= 999999.99;
    }

    /**
     * Escapa saída HTML
     *
     * @param string $string String a ser escapada
     * @return string
     */
    public function escape_html($string) {
        return esc_html($string);
    }

    /**
     * Escapa atributos HTML
     *
     * @param string $string String a ser escapada
     * @return string
     */
    public function escape_attr($string) {
        return esc_attr($string);
    }

    /**
     * Escapa URL
     *
     * @param string $url URL a ser escapada
     * @return string
     */
    public function escape_url($url) {
        return esc_url($url);
    }

    /**
     * Verifica se o usuário tem permissão para editar produtos
     *
     * @return bool
     */
    public function can_edit_products() {
        return current_user_can('edit_products');
    }

    /**
     * Verifica se o usuário tem permissão para editar um produto específico
     *
     * @param int $product_id ID do produto
     * @return bool
     */
    public function can_edit_product($product_id) {
        return current_user_can('edit_post', $product_id);
    }

    /**
     * Log de atividades de segurança
     *
     * @param string $message Mensagem do log
     * @param string $level Nível do log (info, warning, error)
     */
    public function log_security_event($message, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[DW PIX Security] %s: %s', strtoupper($level), $message));
        }
    }
}

