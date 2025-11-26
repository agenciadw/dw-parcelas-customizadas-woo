<?php
/**
 * Classe principal com as funcionalidades do plugin
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe DW_Pix_Core
 */
class DW_Pix_Core {

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
        // Hooks para aplicar preço PIX
        add_action('woocommerce_before_calculate_totals', array($this, 'apply_pix_price'), 10, 1);
        
        // Hook para atualizar checkout quando forma de pagamento muda
        add_action('wp_footer', array($this, 'update_checkout_on_payment_change'));
    }

    /**
     * Aplica o preço PIX no carrinho/checkout
     *
     * @param WC_Cart $cart Objeto do carrinho
     */
    public function apply_pix_price($cart) {
        // Verificações de segurança
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        // Evita loop infinito
        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        // Verifica se a forma de pagamento é PIX
        $chosen_payment_method = WC()->session->get('chosen_payment_method');
        
        if (!$this->is_pix_payment($chosen_payment_method)) {
            return;
        }

        // Aplica preço PIX para cada item do carrinho
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            // Obtém o preço regular correto antes de qualquer modificação
            $regular_price = $this->get_regular_price_for_cart_item($cart_item);
            
            // Se não tem preço regular válido, pula este item
            if ($regular_price <= 0) {
                continue;
            }
            
            $pix_price = $this->get_pix_price_for_cart_item($cart_item);
            
            // Validação rigorosa: só aplica se for um desconto válido
            // O preço PIX deve ser maior que 0, menor que o regular, e não pode ser muito próximo de zero
            if ($pix_price > 0.01 && $pix_price < $regular_price && ($regular_price - $pix_price) > 0.01) {
                $cart_item['data']->set_price($pix_price);
            }
        }
    }

    /**
     * Verifica se o método de pagamento é PIX
     *
     * @param string $payment_method Método de pagamento
     * @return bool
     */
    public function is_pix_payment($payment_method) {
        if (empty($payment_method)) {
            return false;
        }
        
        // Converte para minúsculo para comparação
        $payment_method_lower = strtolower($payment_method);
        
        // Primeiro verifica se contém 'pix' no ID do método
        if (strpos($payment_method_lower, 'pix') !== false) {
            return true;
        }
        
        // Busca nos gateways ativos se algum tem PIX no título/descrição
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        
        if (isset($available_gateways[$payment_method])) {
            $gateway = $available_gateways[$payment_method];
            $title = strtolower($gateway->get_title());
            $method_title = strtolower($gateway->get_method_title());
            
            // Verifica se tem 'pix' no título ou descrição do gateway
            if (strpos($title, 'pix') !== false || strpos($method_title, 'pix') !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtém o preço PIX de um produto
     *
     * @param int $product_id ID do produto
     * @param bool $apply_global_discount Se deve aplicar desconto global se não houver preço individual
     * @param float $regular_price Preço regular (necessário para calcular desconto global)
     * @return float
     */
    public function get_pix_price($product_id, $apply_global_discount = false, $regular_price = 0) {
        // Valida preço regular se fornecido
        $regular_price = floatval($regular_price);
        
        // Para produtos, sempre usar get_post_meta pois HPOS não afeta produtos
        $pix_price = get_post_meta($product_id, '_pix_price', true);
        
        // Se tem preço PIX individual, valida e usa ele
        if (!empty($pix_price) && is_numeric($pix_price)) {
            $pix_price = floatval($pix_price);
            
            // Só retorna se for um valor válido e menor que o preço regular (se fornecido)
            if ($pix_price > 0) {
                if ($regular_price > 0 && $pix_price < $regular_price) {
                    return $pix_price;
                } elseif ($regular_price <= 0) {
                    // Se não tem preço regular para comparar, retorna o preço PIX
                    return $pix_price;
                }
            }
        }
        
        // Se não tem preço individual e deve aplicar desconto global
        if ($apply_global_discount && $regular_price > 0) {
            return $this->calculate_price_with_global_discount($regular_price);
        }
        
        return 0;
    }

    /**
     * Calcula preço com desconto global
     *
     * @param float $regular_price Preço regular
     * @return float
     */
    public function calculate_price_with_global_discount($regular_price) {
        // Valida preço regular
        $regular_price = floatval($regular_price);
        if ($regular_price <= 0) {
            return 0;
        }
        
        $global_settings = $this->get_global_settings();
        $global_discount = isset($global_settings['global_discount']) ? trim($global_settings['global_discount']) : '';
        
        // Se não tem desconto global configurado, retorna 0
        if (empty($global_discount)) {
            return 0;
        }
        
        $global_discount = floatval($global_discount);
        
        // Valida se o desconto está no range válido
        if ($global_discount <= 0 || $global_discount > 100) {
            return 0;
        }
        
        // Calcula o desconto
        $discount_amount = ($regular_price * $global_discount) / 100;
        $pix_price = $regular_price - $discount_amount;
        
        // Valida se o preço PIX é menor que o regular e maior que zero
        if ($pix_price > 0 && $pix_price < $regular_price) {
            return $pix_price;
        }
        
        return 0;
    }

    /**
     * Obtém configurações globais
     *
     * @return array
     */
    private function get_global_settings() {
        if (class_exists('DW_Pix_Settings')) {
            return DW_Pix_Settings::get_global_settings();
        }
        
        return array(
            'global_discount' => '',
            'show_in_gallery' => '0'
        );
    }

    /**
     * Obtém o preço PIX para um item do carrinho (considera variações e desconto global)
     *
     * @param array $cart_item Item do carrinho
     * @return float
     */
    public function get_pix_price_for_cart_item($cart_item) {
        $product = $cart_item['data'];
        
        // Obtém preço regular corretamente
        $regular_price = $this->get_regular_price_for_cart_item($cart_item);
        
        // Se não tem preço regular válido, não aplica desconto
        if ($regular_price <= 0) {
            return 0;
        }
        
        // Se é uma variação, verifica primeiro o preço da variação
        if (isset($cart_item['variation_id']) && $cart_item['variation_id'] > 0) {
            $variation_pix_price = get_post_meta($cart_item['variation_id'], '_pix_price', true);
            
            if (!empty($variation_pix_price) && is_numeric($variation_pix_price)) {
                $pix_price = floatval($variation_pix_price);
                
                // Valida se é um desconto válido
                if ($pix_price > 0 && $pix_price < $regular_price) {
                    return $pix_price;
                }
            }
            
            // Se não tem preço PIX individual na variação, tenta desconto global
            $pix_price = $this->calculate_price_with_global_discount($regular_price);
            if ($pix_price > 0 && $pix_price < $regular_price) {
                return $pix_price;
            }
            
            return 0;
        }
        
        // Se não é uma variação, usa o preço do produto principal
        $product_id = $cart_item['product_id'];
        $pix_price = $this->get_pix_price($product_id, true, $regular_price);
        
        // Validação final: só retorna se for um desconto válido
        if ($pix_price > 0 && $pix_price < $regular_price) {
            return $pix_price;
        }
        
        return 0;
    }

    /**
     * Obtém o preço regular correto para um item do carrinho
     *
     * @param array $cart_item Item do carrinho
     * @return float
     */
    private function get_regular_price_for_cart_item($cart_item) {
        // Se é uma variação, obtém o preço regular da variação diretamente do meta
        if (isset($cart_item['variation_id']) && $cart_item['variation_id'] > 0) {
            $variation_regular_price = get_post_meta($cart_item['variation_id'], '_regular_price', true);
            
            if (!empty($variation_regular_price) && is_numeric($variation_regular_price)) {
                $regular_price = floatval($variation_regular_price);
                if ($regular_price > 0) {
                    return $regular_price;
                }
            }
            
            // Se não tem preço regular na variação, tenta o preço de venda
            $variation_sale_price = get_post_meta($cart_item['variation_id'], '_sale_price', true);
            $variation_price = get_post_meta($cart_item['variation_id'], '_price', true);
            
            // Usa o preço de venda se existir, senão usa o preço normal
            if (!empty($variation_price) && is_numeric($variation_price)) {
                $price = floatval($variation_price);
                if ($price > 0) {
                    return $price;
                }
            }
        }
        
        // Para produtos simples ou se não encontrou preço da variação, usa o objeto do produto
        $product = $cart_item['data'];
        
        // Tenta obter o preço regular do meta primeiro (mais confiável)
        $product_id = $cart_item['product_id'];
        $product_regular_price = get_post_meta($product_id, '_regular_price', true);
        
        if (!empty($product_regular_price) && is_numeric($product_regular_price)) {
            $regular_price = floatval($product_regular_price);
            if ($regular_price > 0) {
                return $regular_price;
            }
        }
        
        // Fallback: usa o método do objeto do produto
        $regular_price = floatval($product->get_regular_price());
        
        // Se o preço regular for 0, tenta o preço atual
        if ($regular_price <= 0) {
            $current_price = floatval($product->get_price());
            if ($current_price > 0) {
                return $current_price;
            }
        }
        
        return $regular_price;
    }

    /**
     * Calcula o desconto PIX
     *
     * @param float $regular_price Preço regular
     * @param float $pix_price Preço PIX
     * @return array Array com amount e percentage
     */
    public function calculate_pix_discount($regular_price, $pix_price) {
        // Validações rigorosas
        $regular_price = floatval($regular_price);
        $pix_price = floatval($pix_price);
        
        // Se não tem valores válidos, retorna zero
        if ($regular_price <= 0 || $pix_price <= 0 || $pix_price >= $regular_price) {
            return array(
                'amount' => 0,
                'percentage' => 0
            );
        }
        
        // Valida se o preço regular não é suspeito (muito baixo)
        if ($regular_price < 1.00) {
            return array(
                'amount' => 0,
                'percentage' => 0
            );
        }

        $discount_amount = $regular_price - $pix_price;
        $discount_percentage = ($discount_amount / $regular_price) * 100;
        
        // Valida se o desconto não é 100% ou muito próximo disso
        if ($discount_percentage >= 99.9 || $discount_percentage <= 0) {
            return array(
                'amount' => 0,
                'percentage' => 0
            );
        }

        return array(
            'amount' => $discount_amount,
            'percentage' => $discount_percentage
        );
    }

    /**
     * Verifica se o carrinho tem produtos com preço PIX
     *
     * @return bool
     */
    public function cart_has_pix_products() {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $pix_price = $this->get_pix_price_for_cart_item($cart_item);
            
            if ($pix_price > 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Atualiza o carrinho quando a forma de pagamento muda
     */
    public function update_checkout_on_payment_change() {
        if (is_checkout() && !is_wc_endpoint_url()) {
            ?>
            <script type="text/javascript">
            jQuery(function($) {
                $('form.checkout').on('change', 'input[name="payment_method"]', function() {
                    $(document.body).trigger('update_checkout');
                });
            });
            </script>
            <?php
        }
    }

    /**
     * Verifica se HPOS está ativo
     *
     * @return bool
     */
    public function is_hpos_enabled() {
        return class_exists('DW_Pix_HPOS') && DW_Pix_HPOS::is_hpos_enabled();
    }

    /**
     * Log de debug para HPOS
     *
     * @param string $message Mensagem
     * @param mixed $data Dados adicionais
     */
    public function log_hpos($message, $data = null) {
        if (class_exists('DW_Pix_HPOS')) {
            DW_Pix_HPOS::log($message, $data);
        }
    }
}
