<?php
/**
 * Classe para compatibilidade com HPOS (High-Performance Order Storage)
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe DW_Parcelas_HPOS
 */
class DW_Parcelas_HPOS {

    /**
     * Verifica se HPOS está ativo
     *
     * @return bool
     */
    public static function is_hpos_enabled() {
        // Verifica se WooCommerce está ativo
        if (!function_exists('wc_get_order')) {
            return false;
        }
        
        if (!class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return false;
        }
        
        try {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtém o ID do pedido de forma compatível com HPOS
     *
     * @param WC_Order|int $order Pedido ou ID do pedido
     * @return int
     */
    public static function get_order_id($order) {
        if (is_numeric($order)) {
            return (int) $order;
        }
        
        if ($order instanceof WC_Order) {
            return $order->get_id();
        }
        
        return 0;
    }

    /**
     * Obtém um pedido de forma compatível com HPOS
     *
     * @param int $order_id ID do pedido
     * @return WC_Order|null
     */
    public static function get_order($order_id) {
        if (self::is_hpos_enabled()) {
            return wc_get_order($order_id);
        }
        
        return wc_get_order($order_id);
    }

    /**
     * Verifica se um ID é de um pedido válido
     *
     * @param int $order_id ID do pedido
     * @return bool
     */
    public static function is_order($order_id) {
        if (self::is_hpos_enabled()) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::is_order($order_id);
        }
        
        return wc_get_order($order_id) !== false;
    }

    /**
     * Obtém o tipo de post para pedidos
     *
     * @return string
     */
    public static function get_order_type() {
        if (self::is_hpos_enabled()) {
            try {
                return \Automattic\WooCommerce\Utilities\OrderUtil::get_order_type('order');
            } catch (Exception $e) {
                return 'shop_order';
            }
        }
        
        return 'shop_order';
    }

    /**
     * Obtém o tipo de post para refunds
     *
     * @return string
     */
    public static function get_order_refund_type() {
        if (self::is_hpos_enabled()) {
            try {
                return \Automattic\WooCommerce\Utilities\OrderUtil::get_order_type('refund');
            } catch (Exception $e) {
                return 'shop_order_refund';
            }
        }
        
        return 'shop_order_refund';
    }

    /**
     * Verifica se um post é um pedido
     *
     * @param WP_Post|int $post Post ou ID do post
     * @return bool
     */
    public static function is_order_post($post) {
        if (self::is_hpos_enabled()) {
            try {
                return \Automattic\WooCommerce\Utilities\OrderUtil::is_order($post);
            } catch (Exception $e) {
                // Fallback para método tradicional
            }
        }
        
        if (is_numeric($post)) {
            $post = get_post($post);
        }
        
        return $post && $post->post_type === 'shop_order';
    }

    /**
     * Obtém meta data de um pedido de forma compatível
     *
     * @param int $order_id ID do pedido
     * @param string $key Chave do meta
     * @param bool $single Se deve retornar um único valor
     * @return mixed
     */
    public static function get_order_meta($order_id, $key, $single = true) {
        if (self::is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                return $order->get_meta($key, $single);
            }
            return false;
        }
        
        return get_post_meta($order_id, $key, $single);
    }

    /**
     * Atualiza meta data de um pedido de forma compatível
     *
     * @param int $order_id ID do pedido
     * @param string $key Chave do meta
     * @param mixed $value Valor do meta
     * @return bool|int
     */
    public static function update_order_meta($order_id, $key, $value) {
        if (self::is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->update_meta_data($key, $value);
                return $order->save();
            }
            return false;
        }
        
        return update_post_meta($order_id, $key, $value);
    }

    /**
     * Remove meta data de um pedido de forma compatível
     *
     * @param int $order_id ID do pedido
     * @param string $key Chave do meta
     * @return bool
     */
    public static function delete_order_meta($order_id, $key) {
        if (self::is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->delete_meta_data($key);
                return $order->save();
            }
            return false;
        }
        
        return delete_post_meta($order_id, $key);
    }

    /**
     * Obtém o status de um pedido de forma compatível
     *
     * @param int $order_id ID do pedido
     * @return string
     */
    public static function get_order_status($order_id) {
        if (self::is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                return $order->get_status();
            }
            return '';
        }
        
        return get_post_status($order_id);
    }

    /**
     * Atualiza o status de um pedido de forma compatível
     *
     * @param int $order_id ID do pedido
     * @param string $status Novo status
     * @return bool
     */
    public static function update_order_status($order_id, $status) {
        if (self::is_hpos_enabled()) {
            $order = wc_get_order($order_id);
            if ($order) {
                $order->set_status($status);
                return $order->save();
            }
            return false;
        }
        
        return wp_update_post(array(
            'ID' => $order_id,
            'post_status' => $status
        ));
    }

    /**
     * Log de debug para HPOS
     *
     * @param string $message Mensagem
     * @param mixed $data Dados adicionais
     */
    public static function log($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = '[DW Parcelas HPOS] ' . $message;
            if ($data !== null) {
                $log_message .= ' - Data: ' . print_r($data, true);
            }
            error_log($log_message);
        }
    }
}

