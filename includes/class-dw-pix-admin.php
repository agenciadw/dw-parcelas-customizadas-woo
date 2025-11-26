<?php
/**
 * Classe para funcionalidades administrativas
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe DW_Pix_Admin
 */
class DW_Pix_Admin {

    /**
     * Construtor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Inicializa os hooks
     */
    private function init_hooks() {
        // Hooks para campos do produto
        add_action('woocommerce_product_options_pricing', array($this, 'add_pix_price_field'));
        add_action('woocommerce_process_product_meta', array($this, 'save_pix_price_field'));
        
        // Hooks para variações de produto
        add_action('woocommerce_variation_options_pricing', array($this, 'add_pix_price_field_variation'), 10, 3);
        add_action('woocommerce_save_product_variation', array($this, 'save_pix_price_field_variation'), 10, 2);
        
        // Hook para adicionar coluna na listagem de produtos
        add_filter('manage_edit-product_columns', array($this, 'add_pix_price_column'));
        add_action('manage_product_posts_custom_column', array($this, 'display_pix_price_column'), 10, 2);
    }

    /**
     * Adiciona campo de preço PIX no painel do produto
     */
    public function add_pix_price_field() {
        global $post;
        
        if (!$post) {
            return;
        }
        
        echo '<div class="options_group">';
        
        woocommerce_wp_text_input(array(
            'id' => '_pix_price',
            'label' => __('Preço no PIX (R$)', 'dw-price-to-pix'),
            'placeholder' => __('Ex: 92.00', 'dw-price-to-pix'),
            'desc_tip' => true,
            'description' => __('Defina o preço especial para pagamento via PIX. Deixe vazio para usar o preço normal.', 'dw-price-to-pix'),
            'type' => 'text',
            'data_type' => 'price',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '0'
            )
        ));
        
        echo '</div>';
    }

    /**
     * Adiciona campo de preço PIX para variações de produto
     *
     * @param int $loop Índice do loop
     * @param array $variation_data Dados da variação
     * @param WP_Post $variation Objeto da variação
     */
    public function add_pix_price_field_variation($loop, $variation_data, $variation) {
        $pix_price = get_post_meta($variation->ID, '_pix_price', true);
        
        echo '<div class="form-row form-row-first">';
        woocommerce_wp_text_input(array(
            'id' => '_pix_price[' . $loop . ']',
            'name' => '_pix_price[' . $loop . ']',
            'label' => __('Preço PIX (R$)', 'dw-price-to-pix'),
            'placeholder' => __('Ex: 92.00', 'dw-price-to-pix'),
            'desc_tip' => true,
            'description' => __('Preço especial para PIX nesta variação', 'dw-price-to-pix'),
            'value' => $pix_price,
            'type' => 'text',
            'data_type' => 'price',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '0'
            )
        ));
        echo '</div>';
    }

    /**
     * Salva o campo de preço PIX
     *
     * @param int $post_id ID do post
     */
    public function save_pix_price_field($post_id) {
        // Verificações de segurança
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['_pix_price'])) {
            return;
        }

        $pix_price = $this->sanitize_price_input($_POST['_pix_price']);
        
        if ($pix_price > 0) {
            update_post_meta($post_id, '_pix_price', $pix_price);
        } else {
            delete_post_meta($post_id, '_pix_price');
        }
    }

    /**
     * Salva o campo de preço PIX para variações
     *
     * @param int $variation_id ID da variação
     * @param int $loop Índice do loop
     */
    public function save_pix_price_field_variation($variation_id, $loop) {
        if (!isset($_POST['_pix_price'][$loop])) {
            return;
        }

        $pix_price = $this->sanitize_price_input($_POST['_pix_price'][$loop]);
        
        if ($pix_price > 0) {
            update_post_meta($variation_id, '_pix_price', $pix_price);
        } else {
            delete_post_meta($variation_id, '_pix_price');
        }
    }

    /**
     * Sanitiza entrada de preço
     *
     * @param string $price Preço a ser sanitizado
     * @return float
     */
    private function sanitize_price_input($price) {
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
     * Adiciona coluna de preço PIX na listagem de produtos
     *
     * @param array $columns Colunas existentes
     * @return array
     */
    public function add_pix_price_column($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Adiciona após a coluna de preço
            if ($key === 'price') {
                $new_columns['pix_price'] = __('Preço PIX', 'dw-price-to-pix');
            }
        }
        
        return $new_columns;
    }

    /**
     * Exibe o preço PIX na coluna da listagem
     *
     * @param string $column Nome da coluna
     * @param int $post_id ID do post
     */
    public function display_pix_price_column($column, $post_id) {
        if ($column === 'pix_price') {
            $pix_price = get_post_meta($post_id, '_pix_price', true);
            
            if (!empty($pix_price) && $pix_price > 0) {
                echo '<span style="color: #4caf50; font-weight: bold;">' . wc_price($pix_price) . '</span>';
            } else {
                echo '<span style="color: #999;">—</span>';
            }
        }
    }
}

