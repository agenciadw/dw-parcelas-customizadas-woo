<?php
/**
 * Classe principal para cálculo de parcelas de cartão de crédito
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe DW_Parcelas_Installments_Core
 */
class DW_Parcelas_Installments_Core {

    /**
     * Obtém as configurações de parcelas
     *
     * @return array
     */
    public static function get_settings() {
        if (class_exists('DW_Pix_Settings')) {
            return DW_Pix_Settings::get_installments_settings();
        }
        
        return self::get_default_settings();
    }

    /**
     * Retorna configurações padrão
     *
     * @return array
     */
    private static function get_default_settings() {
        return array(
            'enabled' => '0',
            'max_installments' => 12,
            'installments_without_interest' => 3,
            'interest_rate' => '2.99',
            'min_installment_value' => 5.00,
            'table_display_type' => 'accordion',
            'show_table' => '1',
            'text_before_installments' => '',
            'text_after_installments' => ''
        );
    }

    /**
     * Verifica se as parcelas estão ativas
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = self::get_settings();
        return isset($settings['enabled']) && $settings['enabled'] === '1';
    }

    /**
     * Calcula todas as parcelas para um produto
     *
     * @param float $price Preço do produto
     * @param int $product_id ID do produto (opcional, para produtos variáveis)
     * @return array Array de parcelas [parcelas => [numero => [valor => float, total => float, has_interest => bool]]]
     */
    public static function calculate_installments($price, $product_id = 0) {
        if (!self::is_enabled()) {
            return array();
        }

        $price = floatval($price);
        if ($price <= 0) {
            return array();
        }

        $settings = self::get_settings();
        $max_installments = intval($settings['max_installments']);
        $installments_without_interest = intval($settings['installments_without_interest']);
        $interest_rate = floatval($settings['interest_rate']);
        $min_installment_value = floatval($settings['min_installment_value']);

        $installments = array();
        
        for ($i = 1; $i <= $max_installments; $i++) {
            $has_interest = $i > $installments_without_interest;
            
            if ($has_interest) {
                // Fórmula de juros compostos: PV * (1 + i)^n
                $total = $price * pow(1 + ($interest_rate / 100), $i);
                $installment_value = $total / $i;
            } else {
                // Sem juros
                $total = $price;
                $installment_value = $price / $i;
            }
            
            // Verifica se a parcela mínima foi atingida
            if ($installment_value < $min_installment_value) {
                break;
            }
            
            $installments[$i] = array(
                'numero' => $i,
                'valor' => round($installment_value, 2),
                'total' => round($total, 2),
                'has_interest' => $has_interest
            );
        }
        
        return $installments;
    }

    /**
     * Obtém a melhor condição de parcelamento (mais parcelas sem juros)
     *
     * @param float $price Preço do produto
     * @param int $product_id ID do produto (opcional)
     * @return array|null Array com numero, valor, total, has_interest ou null
     */
    public static function get_best_installment($price, $product_id = 0) {
        $installments = self::calculate_installments($price, $product_id);
        
        if (empty($installments)) {
            return null;
        }
        
        // Retorna a última parcela (mais parcelas sem juros, ou mais parcelas no geral)
        return end($installments);
    }

    /**
     * Formata o texto da melhor condição
     *
     * @param float $price Preço do produto
     * @param int $product_id ID do produto (opcional)
     * @return string
     */
    public static function format_best_installment_text($price, $product_id = 0) {
        $best = self::get_best_installment($price, $product_id);
        
        if (!$best) {
            return '';
        }
        
        $price_formatted = wc_price($price);
        $installment_value = wc_price($best['valor']);
        $installment_number = $best['numero'];
        $interest_text = $best['has_interest'] ? '' : ' sem juros';
        
        return sprintf(
            __('ou %s em até %dx de %s%s', 'dw-parcelas-customizadas-woo'),
            $price_formatted,
            $installment_number,
            $installment_value,
            $interest_text
        );
    }

    /**
     * Obtém o preço do produto considerando variações
     *
     * @param WC_Product $product Objeto do produto
     * @return float
     */
    public static function get_product_price($product) {
        if (!$product) {
            return 0;
        }
        
        // Para produtos variáveis, retorna o menor preço disponível
        if ($product->is_type('variable')) {
            $prices = $product->get_variation_prices(true);
            if (isset($prices['price']) && !empty($prices['price'])) {
                return floatval(min($prices['price']));
            }
        }
        
        // Para produtos com preço de venda, usa ele, senão usa preço regular
        $price = $product->get_sale_price();
        if (empty($price) || $price <= 0) {
            $price = $product->get_regular_price();
        }
        
        return floatval($price);
    }
}

