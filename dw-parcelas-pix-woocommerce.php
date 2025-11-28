<?php
/**
 * Plugin Name: DW Parcelas e Pix Customizadas WooCommerce
 * Plugin URI: https://github.com/agenciadw/dw-parcelas-customizadas-woo
 * Description: Gerencie preços customizados para PIX e exiba parcelas de cartão de crédito de forma profissional no WooCommerce
 * Version: 0.2.0
 * Author: David William da Costa
 * Author URI: https://github.com/agenciadw
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dw-parcelas-customizadas-woo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * HPOS: Compatible
 * Elementor: Compatible
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Define constantes do plugin
define('DW_PARCELAS_PLUGIN_FILE', __FILE__);
define('DW_PARCELAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DW_PARCELAS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DW_PARCELAS_VERSION', '0.2.0');

// Aliases para compatibilidade com código antigo do PIX
if (!defined('DW_PIX_PLUGIN_FILE')) {
    define('DW_PIX_PLUGIN_FILE', __FILE__);
}
if (!defined('DW_PIX_PLUGIN_DIR')) {
    define('DW_PIX_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('DW_PIX_PLUGIN_URL')) {
    define('DW_PIX_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('DW_PIX_VERSION')) {
    define('DW_PIX_VERSION', '0.2.0');
}

/**
 * Classe principal do plugin
 */
class DW_Parcelas_Pix_WooCommerce {

    /**
     * Instância única do plugin
     */
    private static $instance = null;

    /**
     * Retorna a instância única do plugin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Inicializa os hooks do WordPress
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Carrega as dependências do plugin
     */
    private function load_dependencies() {
        // Carrega classes principais
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-parcelas-config.php';
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-parcelas-hpos.php';
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-pix-security.php';
        
        // Cria aliases para compatibilidade com código antigo
        if (!class_exists('DW_Pix_Config')) {
            class_alias('DW_Parcelas_Config', 'DW_Pix_Config');
        }
        if (!class_exists('DW_Pix_HPOS')) {
            class_alias('DW_Parcelas_HPOS', 'DW_Pix_HPOS');
        }
        
        // Carrega funcionalidades principais
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-pix-core.php';
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-parcelas-installments-core.php';
        
        // Carrega interfaces admin e frontend
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-pix-admin.php';
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-pix-frontend.php';
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-parcelas-frontend.php';
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-pix-settings.php';
        
        // Carrega integração com Elementor (será inicializada quando o Elementor carregar)
        require_once DW_PARCELAS_PLUGIN_DIR . 'includes/class-dw-elementor-integration.php';
    }

    /**
     * Inicializa o plugin
     */
    public function init() {
        // Verifica se o WooCommerce está ativo
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Declara compatibilidade com HPOS
        add_action('before_woocommerce_init', array($this, 'declare_hpos_compatibility'));

        // Inicializa as classes principais
        new DW_Pix_Core();
        new DW_Pix_Admin();
        new DW_Pix_Frontend();
        new DW_Parcelas_Frontend();
        new DW_Pix_Settings();
    }

    /**
     * Declara compatibilidade com HPOS
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }

    /**
     * Carrega o arquivo de tradução
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'dw-parcelas-customizadas-woo',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Aviso quando WooCommerce não está ativo
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' . 
             esc_html__('DW Parcelas e Pix Customizadas WooCommerce', 'dw-parcelas-customizadas-woo') . 
             '</strong> ' . 
             esc_html__('requer que o WooCommerce esteja instalado e ativo.', 'dw-parcelas-customizadas-woo') . 
             '</p></div>';
    }

    /**
     * Ativação do plugin
     */
    public function activate() {
        // Verifica se o WooCommerce está ativo
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('Este plugin requer o WooCommerce para funcionar.', 'dw-parcelas-customizadas-woo'),
                esc_html__('Plugin Desativado', 'dw-parcelas-customizadas-woo'),
                array('back_link' => true)
            );
        }

        // Define versão do plugin
        update_option('dw_parcelas_version', DW_PARCELAS_VERSION);
    }

    /**
     * Desativação do plugin
     */
    public function deactivate() {
        // Limpa cache se necessário
        wp_cache_flush();
    }
}

// Inicializa o plugin
DW_Parcelas_Pix_WooCommerce::get_instance();

