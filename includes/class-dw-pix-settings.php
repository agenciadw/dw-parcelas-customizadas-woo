<?php
/**
 * Classe para configurações do plugin
 *
 * @package DW_Parcelas_Pix_WooCommerce
 */

// Evita acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe DW_Pix_Settings
 */
class DW_Pix_Settings {

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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Adiciona menu no admin
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('DW Parcelas e Pix - Configurações', 'dw-price-to-pix'),
            __('Parcelas e PIX', 'dw-price-to-pix'),
            'manage_woocommerce',
            'dw-pix-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Inicializa as configurações
     */
    public function init_settings() {
        // Registra settings com sanitizers que fazem merge dos dados existentes
        register_setting('dw_pix_settings', 'dw_pix_design_settings', array($this, 'sanitize_design_settings'));
        register_setting('dw_pix_settings', 'dw_pix_global_settings', array($this, 'sanitize_global_settings'));
        register_setting('dw_pix_settings', 'dw_pix_installments_settings', array($this, 'sanitize_installments_settings'));
        register_setting('dw_pix_settings', 'dw_pix_installments_design_settings', array($this, 'sanitize_installments_design_settings'));
        
        // Seção de configurações globais
        add_settings_section(
            'dw_pix_global_section',
            __('Configurações Globais PIX', 'dw-price-to-pix'),
            array($this, 'global_section_callback'),
            'dw_pix_settings'
        );

        // Campo de desconto global
        add_settings_field(
            'global_discount',
            __('Desconto Global PIX (%)', 'dw-price-to-pix'),
            array($this, 'global_discount_callback'),
            'dw_pix_settings',
            'dw_pix_global_section'
        );

        // Campo para exibir na galeria
        add_settings_field(
            'show_in_gallery',
            __('Exibir na Galeria de Produtos', 'dw-price-to-pix'),
            array($this, 'show_in_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_global_section'
        );
        
        // Campo de posição do PIX
        add_settings_field(
            'pix_position',
            __('Posição do PIX na Página do Produto', 'dw-price-to-pix'),
            array($this, 'pix_position_callback'),
            'dw_pix_settings',
            'dw_pix_global_section'
        );
        
        // Seção de design
        add_settings_section(
            'dw_pix_design_section',
            __('Configurações de Design', 'dw-price-to-pix'),
            array($this, 'design_section_callback'),
            'dw_pix_settings'
        );

        // Campo de cor de fundo
        add_settings_field(
            'background_color',
            __('Cor de Fundo', 'dw-price-to-pix'),
            array($this, 'background_color_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de cor da borda
        add_settings_field(
            'border_color',
            __('Cor da Borda', 'dw-price-to-pix'),
            array($this, 'border_color_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        // Campo para remover borda
        add_settings_field(
            'hide_border',
            __('Remover Borda', 'dw-price-to-pix'),
            array($this, 'hide_border_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de cor do texto
        add_settings_field(
            'text_color',
            __('Cor do Texto', 'dw-price-to-pix'),
            array($this, 'text_color_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de cor do preço
        add_settings_field(
            'price_color',
            __('Cor do Preço', 'dw-price-to-pix'),
            array($this, 'price_color_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de ícone personalizado para página do produto
        add_settings_field(
            'pix_icon_custom',
            __('Ícone PIX - Página do Produto', 'dw-price-to-pix'),
            array($this, 'pix_icon_custom_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        // Campo para exibir ícone PIX na galeria
        add_settings_field(
            'show_pix_icon_gallery',
            __('Exibir Ícone PIX na Galeria', 'dw-price-to-pix'),
            array($this, 'show_pix_icon_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        // Campo de ícone personalizado para galeria
        add_settings_field(
            'pix_icon_custom_gallery',
            __('Ícone PIX - Galeria de Produtos', 'dw-price-to-pix'),
            array($this, 'pix_icon_custom_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        // Campos de estilo visual para PIX - Página do Produto
        add_settings_field(
            'pix_margin_product',
            __('Margem - PIX (Página do Produto)', 'dw-price-to-pix'),
            array($this, 'pix_margin_product_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        add_settings_field(
            'pix_padding_product',
            __('Preenchimento - PIX (Página do Produto)', 'dw-price-to-pix'),
            array($this, 'pix_padding_product_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        // Campos de estilo visual para PIX - Galeria
        add_settings_field(
            'pix_margin_gallery',
            __('Margem - PIX (Galeria de Produtos)', 'dw-price-to-pix'),
            array($this, 'pix_margin_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        add_settings_field(
            'pix_padding_gallery',
            __('Preenchimento - PIX (Galeria de Produtos)', 'dw-price-to-pix'),
            array($this, 'pix_padding_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        add_settings_field(
            'pix_border_radius',
            __('Raio da Borda - PIX', 'dw-price-to-pix'),
            array($this, 'pix_border_radius_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        // Campo permitir fundo transparente para PIX
        add_settings_field(
            'allow_transparent_background_pix',
            __('Permitir Fundo Transparente', 'dw-price-to-pix'),
            array($this, 'allow_transparent_background_pix_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de texto personalizado
        add_settings_field(
            'custom_text',
            __('Texto Personalizado', 'dw-price-to-pix'),
            array($this, 'custom_text_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de estilo da borda
        add_settings_field(
            'border_style',
            __('Estilo da Borda', 'dw-price-to-pix'),
            array($this, 'border_style_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de tamanho da fonte
        add_settings_field(
            'font_size',
            __('Tamanho da Fonte', 'dw-price-to-pix'),
            array($this, 'font_size_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );

        // Campo de texto de desconto
        add_settings_field(
            'discount_text',
            __('Texto de Desconto', 'dw-price-to-pix'),
            array($this, 'discount_text_callback'),
            'dw_pix_settings',
            'dw_pix_design_section'
        );
        
        // Seção de configurações de Cartão de Crédito
        add_settings_section(
            'dw_pix_installments_section',
            __('Configurações de Cartão de Crédito', 'dw-price-to-pix'),
            array($this, 'installments_section_callback'),
            'dw_pix_settings'
        );
        
        // Campo ativar/desativar parcelas
        add_settings_field(
            'installments_enabled',
            __('Ativar Parcelas', 'dw-price-to-pix'),
            array($this, 'installments_enabled_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo máximo de parcelas
        add_settings_field(
            'max_installments',
            __('Máximo de Parcelas', 'dw-price-to-pix'),
            array($this, 'max_installments_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo parcelas sem juros
        add_settings_field(
            'installments_without_interest',
            __('Parcelas sem Juros', 'dw-price-to-pix'),
            array($this, 'installments_without_interest_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo taxa de juros
        add_settings_field(
            'interest_rate',
            __('Taxa de Juros (% ao mês)', 'dw-price-to-pix'),
            array($this, 'interest_rate_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo parcela mínima
        add_settings_field(
            'min_installment_value',
            __('Parcela Mínima (R$)', 'dw-price-to-pix'),
            array($this, 'min_installment_value_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo exibir tabela
        add_settings_field(
            'show_table',
            __('Exibir Tabela de Parcelas', 'dw-price-to-pix'),
            array($this, 'show_table_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo tipo de exibição da tabela
        add_settings_field(
            'table_display_type',
            __('Tipo de Exibição da Tabela', 'dw-price-to-pix'),
            array($this, 'table_display_type_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo texto antes das parcelas
        add_settings_field(
            'text_before_installments',
            __('Texto Antes das Parcelas', 'dw-price-to-pix'),
            array($this, 'text_before_installments_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo texto após as parcelas
        add_settings_field(
            'text_after_installments',
            __('Texto Após as Parcelas', 'dw-price-to-pix'),
            array($this, 'text_after_installments_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo visibilidade - onde exibir
        add_settings_field(
            'display_locations',
            __('Onde Exibir Parcelas', 'dw-price-to-pix'),
            array($this, 'display_locations_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Campo posição no produto único
        add_settings_field(
            'product_position',
            __('Posição na Página do Produto', 'dw-price-to-pix'),
            array($this, 'product_position_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
        
        // Seção de design das parcelas
        add_settings_section(
            'dw_pix_installments_design_section',
            __('Configurações de Design - Parcelas', 'dw-price-to-pix'),
            array($this, 'installments_design_section_callback'),
            'dw_pix_settings'
        );
        
        // Campo de cor de fundo do resumo
        add_settings_field(
            'installments_background_color',
            __('Cor de Fundo do Resumo', 'dw-price-to-pix'),
            array($this, 'installments_background_color_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo de cor da borda
        add_settings_field(
            'installments_border_color',
            __('Cor da Borda', 'dw-price-to-pix'),
            array($this, 'installments_border_color_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo de cor do texto
        add_settings_field(
            'installments_text_color',
            __('Cor do Texto', 'dw-price-to-pix'),
            array($this, 'installments_text_color_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo de cor do preço
        add_settings_field(
            'installments_price_color',
            __('Cor do Preço', 'dw-price-to-pix'),
            array($this, 'installments_price_color_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo de estilo da borda
        add_settings_field(
            'installments_border_style',
            __('Estilo da Borda', 'dw-price-to-pix'),
            array($this, 'installments_border_style_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo de tamanho da fonte
        add_settings_field(
            'installments_font_size',
            __('Tamanho da Fonte', 'dw-price-to-pix'),
            array($this, 'installments_font_size_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo de ícone do cartão para página do produto
        add_settings_field(
            'credit_card_icon_custom',
            __('Ícone do Cartão - Página do Produto', 'dw-price-to-pix'),
            array($this, 'credit_card_icon_custom_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo para exibir ícone do cartão na galeria
        add_settings_field(
            'show_credit_card_icon_gallery',
            __('Exibir Ícone do Cartão na Galeria', 'dw-price-to-pix'),
            array($this, 'show_credit_card_icon_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo de ícone do cartão para galeria
        add_settings_field(
            'credit_card_icon_custom_gallery',
            __('Ícone do Cartão - Galeria de Produtos', 'dw-price-to-pix'),
            array($this, 'credit_card_icon_custom_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campos de estilo visual para Parcelas - Página do Produto
        add_settings_field(
            'installments_margin_product',
            __('Margem - Parcelas (Página do Produto)', 'dw-price-to-pix'),
            array($this, 'installments_margin_product_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        add_settings_field(
            'installments_padding_product',
            __('Preenchimento - Parcelas (Página do Produto)', 'dw-price-to-pix'),
            array($this, 'installments_padding_product_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campos de estilo visual para Parcelas - Galeria
        add_settings_field(
            'installments_margin_gallery',
            __('Margem - Parcelas (Galeria de Produtos)', 'dw-price-to-pix'),
            array($this, 'installments_margin_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        add_settings_field(
            'installments_padding_gallery',
            __('Preenchimento - Parcelas (Galeria de Produtos)', 'dw-price-to-pix'),
            array($this, 'installments_padding_gallery_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        add_settings_field(
            'installments_border_radius',
            __('Raio da Borda - Parcelas', 'dw-price-to-pix'),
            array($this, 'installments_border_radius_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo exibir ícone do cartão
        add_settings_field(
            'show_credit_card_icon',
            __('Exibir Ícone do Cartão', 'dw-price-to-pix'),
            array($this, 'show_credit_card_icon_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo posição do ícone do cartão
        add_settings_field(
            'credit_card_icon_position',
            __('Posição do Ícone', 'dw-price-to-pix'),
            array($this, 'credit_card_icon_position_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo permitir cor transparente
        add_settings_field(
            'allow_transparent_background',
            __('Permitir Fundo Transparente', 'dw-price-to-pix'),
            array($this, 'allow_transparent_background_callback'),
            'dw_pix_settings',
            'dw_pix_installments_design_section'
        );
        
        // Campo textos personalizados por localização
        add_settings_field(
            'location_texts',
            __('Textos por Localização', 'dw-price-to-pix'),
            array($this, 'location_texts_callback'),
            'dw_pix_settings',
            'dw_pix_installments_section'
        );
    }

    /**
     * Callback da seção de configurações globais
     */
    public function global_section_callback() {
        echo '<p>' . __('Configure o desconto global PIX que será aplicado a todos os produtos. Os preços PIX individuais dos produtos terão prioridade.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo desconto global
     */
    public function global_discount_callback() {
        $settings = get_option('dw_pix_global_settings', array());
        $value = isset($settings['global_discount']) ? $settings['global_discount'] : '';
        
        echo '<input type="number" name="dw_pix_global_settings[global_discount]" value="' . esc_attr($value) . '" step="0.01" min="0" max="100" style="width: 150px;" />';
        echo '<span style="margin-left: 10px;">%</span>';
        echo '<p class="description">' . __('Percentual de desconto global para PIX (ex: 10 = 10% de desconto). Deixe vazio para não usar desconto global.', 'dw-price-to-pix') . '</p>';
        echo '<p class="description"><strong>' . __('Importante:', 'dw-price-to-pix') . '</strong> ' . __('Se um produto tiver preço PIX individual configurado, ele terá prioridade sobre o desconto global.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo exibir na galeria
     */
    public function show_in_gallery_callback() {
        $settings = get_option('dw_pix_global_settings', array());
        $value = isset($settings['show_in_gallery']) ? $settings['show_in_gallery'] : '0';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_global_settings[show_in_gallery]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_global_settings[show_in_gallery]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Exibir preço PIX na galeria/listagem de produtos', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando ativado, o preço PIX será exibido abaixo do preço normal na galeria de produtos.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo posição do PIX
     */
    public function pix_position_callback() {
        $settings = get_option('dw_pix_global_settings', array());
        $value = isset($settings['pix_position']) ? $settings['pix_position'] : 'after_installments';
        
        // Simplificado: apenas 2 opções - acima ou abaixo das parcelas
        $options = array(
            'before_installments' => __('Acima das Parcelas', 'dw-price-to-pix'),
            'after_installments' => __('Abaixo das Parcelas (Padrão)', 'dw-price-to-pix')
        );
        
        echo '<select name="dw_pix_global_settings[pix_position]" style="width: 300px;">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('O PIX e as Parcelas sempre aparecem acima do botão de comprar. Escolha se o PIX deve aparecer acima ou abaixo das informações de parcelamento.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback da seção de design
     */
    public function design_section_callback() {
        echo '<p>' . __('Personalize a aparência do box PIX conforme sua marca.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo cor de fundo
     */
    public function background_color_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['background_color']) ? $settings['background_color'] : '#e8f5e9';
        $allow_transparent = isset($settings['allow_transparent_background_pix']) && $settings['allow_transparent_background_pix'] === '1';
        
        // Se valor está vazio ou transparente e permite transparente, usa valor padrão para o color picker
        $color_value = $value;
        if ($allow_transparent && (empty($value) || strtolower($value) === 'transparent')) {
            $color_value = '#e8f5e9'; // Valor padrão para o color picker
        }
        
        echo '<input type="color" name="dw_pix_design_settings[background_color]" value="' . esc_attr($color_value) . '" id="dw-pix-background-color" />';
        echo '<input type="hidden" id="dw-pix-background-color-original" value="' . esc_attr($value) . '" />';
        
        if ($allow_transparent) {
            $is_transparent = (empty($value) || strtolower($value) === 'transparent');
            echo '<br><br>';
            echo '<label>';
            echo '<input type="checkbox" id="dw-pix-use-transparent" ' . checked($is_transparent, true, false) . ' />';
            echo ' ' . __('Usar fundo transparente', 'dw-price-to-pix');
            echo '</label>';
            echo '<script>
            jQuery(document).ready(function($) {
                var $colorInput = $("#dw-pix-background-color");
                var $transparentCheck = $("#dw-pix-use-transparent");
                var originalValue = $("#dw-pix-background-color-original").val();
                
                $transparentCheck.on("change", function() {
                    if ($(this).is(":checked")) {
                        $colorInput.val("transparent").prop("disabled", true);
                        // Cria campo hidden para salvar "transparent"
                        if ($("#dw-pix-bg-transparent-hidden").length === 0) {
                            $colorInput.after(\'<input type="hidden" id="dw-pix-bg-transparent-hidden" name="dw_pix_design_settings[background_color]" value="transparent" />\');
                        }
                        $colorInput.attr("name", "");
                    } else {
                        $colorInput.prop("disabled", false).attr("name", "dw_pix_design_settings[background_color]");
                        $("#dw-pix-bg-transparent-hidden").remove();
                        if (!$colorInput.val() || $colorInput.val() === "transparent") {
                            $colorInput.val("' . esc_js($color_value) . '");
                        }
                    }
                });
                
                // Inicializa estado
                if ($transparentCheck.is(":checked")) {
                    $colorInput.val("transparent").prop("disabled", true);
                    if ($("#dw-pix-bg-transparent-hidden").length === 0) {
                        $colorInput.after(\'<input type="hidden" id="dw-pix-bg-transparent-hidden" name="dw_pix_design_settings[background_color]" value="transparent" />\');
                    }
                    $colorInput.attr("name", "");
                }
            });
            </script>';
        }
        
        echo '<p class="description">' . __('Cor de fundo do box PIX', 'dw-price-to-pix');
        if ($allow_transparent) {
            echo ' ' . __('(marque a opção abaixo para usar fundo transparente)', 'dw-price-to-pix');
        }
        echo '</p>';
    }

    /**
     * Callback do campo cor da borda
     */
    public function border_color_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['border_color']) ? $settings['border_color'] : '#4caf50';
        echo '<input type="color" name="dw_pix_design_settings[border_color]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor da borda esquerda do box PIX', 'dw-price-to-pix') . '</p>';
    }
    
    /**
     * Callback do campo remover borda
     */
    public function hide_border_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['hide_border']) ? $settings['hide_border'] : '0';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_design_settings[hide_border]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_design_settings[hide_border]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Remover borda esquerda', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando marcado, a borda esquerda não será exibida.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo cor do texto
     */
    public function text_color_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['text_color']) ? $settings['text_color'] : '#2e7d32';
        echo '<input type="color" name="dw_pix_design_settings[text_color]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor do texto principal', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo cor do preço
     */
    public function price_color_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['price_color']) ? $settings['price_color'] : '#1b5e20';
        echo '<input type="color" name="dw_pix_design_settings[price_color]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor do preço PIX', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo ícone PIX
     */
    public function pix_icon_custom_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $icon_url = isset($settings['pix_icon_custom']) ? $settings['pix_icon_custom'] : '';
        $default_icon_url = DW_PIX_PLUGIN_URL . 'assets/images/pix-svgrepo-com.svg';
        
        // Se não tem ícone personalizado, usa o padrão
        if (empty($icon_url)) {
            $icon_url = $default_icon_url;
        }
        
        echo '<div class="dw-pix-icon-upload-container">';
        
        // Preview do ícone
        echo '<div class="dw-pix-icon-preview" style="margin-bottom: 10px;">';
        echo '<img id="dw-pix-icon-preview" src="' . esc_url($icon_url) . '" alt="Ícone PIX" data-default="' . esc_url($default_icon_url) . '" style="max-width: 48px; max-height: 48px; display: block; margin-bottom: 10px;" />';
        echo '</div>';
        
        // Campo hidden para URL do ícone
        echo '<input type="hidden" id="dw-pix-icon-url" name="dw_pix_design_settings[pix_icon_custom]" value="' . esc_attr($settings['pix_icon_custom'] ?? '') . '" />';
        
        // Botões
        echo '<div style="display: flex; gap: 10px; align-items: center;">';
        echo '<button type="button" class="button" id="dw-pix-upload-icon">' . __('Escolher Ícone', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-pix-remove-icon" style="' . (empty($settings['pix_icon_custom']) ? 'display: none;' : '') . '">' . __('Remover', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-pix-reset-icon">' . __('Usar Padrão', 'dw-price-to-pix') . '</button>';
        echo '</div>';
        
        echo '</div>';
        
        echo '<p class="description">' . __('Faça upload de um ícone SVG ou PNG personalizado para o PIX na página do produto. Tamanho recomendado: 48x48px ou maior.', 'dw-price-to-pix') . '</p>';
        echo '<p class="description"><strong>' . __('Ícone padrão:', 'dw-price-to-pix') . '</strong> ' . __('Se nenhum ícone for enviado, será usado automaticamente o ícone padrão do plugin (pix-svgrepo-com.svg).', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo exibir ícone PIX na galeria
     */
    public function show_pix_icon_gallery_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['show_pix_icon_gallery']) ? $settings['show_pix_icon_gallery'] : '1';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_design_settings[show_pix_icon_gallery]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_design_settings[show_pix_icon_gallery]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Exibir ícone PIX na galeria de produtos', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando ativado, o ícone PIX será exibido junto com o preço na galeria de produtos.', 'dw-price-to-pix') . '</p>';
    }
    
    /**
     * Callback do campo ícone PIX para galeria
     */
    public function pix_icon_custom_gallery_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $icon_url = isset($settings['pix_icon_custom_gallery']) ? $settings['pix_icon_custom_gallery'] : '';
        $default_icon_url = DW_PIX_PLUGIN_URL . 'assets/images/pix-svgrepo-com.svg';
        
        // Se não tem ícone personalizado, usa o padrão
        if (empty($icon_url)) {
            $icon_url = $default_icon_url;
        }
        
        echo '<div class="dw-pix-icon-upload-container">';
        
        // Preview do ícone
        echo '<div class="dw-pix-icon-preview" style="margin-bottom: 10px;">';
        echo '<img id="dw-pix-icon-gallery-preview" src="' . esc_url($icon_url) . '" alt="Ícone PIX Galeria" data-default="' . esc_url($default_icon_url) . '" style="max-width: 48px; max-height: 48px; display: block; margin-bottom: 10px;" />';
        echo '</div>';
        
        echo '<input type="hidden" id="dw-pix-icon-gallery-url" name="dw_pix_design_settings[pix_icon_custom_gallery]" value="' . esc_attr($settings['pix_icon_custom_gallery'] ?? '') . '" />';
        
        // Botões
        echo '<div style="display: flex; gap: 10px; align-items: center;">';
        echo '<button type="button" class="button" id="dw-pix-upload-icon-gallery">' . __('Escolher Ícone', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-pix-remove-icon-gallery" style="' . (empty($settings['pix_icon_custom_gallery']) ? 'display: none;' : '') . '">' . __('Remover', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-pix-reset-icon-gallery">' . __('Usar Padrão', 'dw-price-to-pix') . '</button>';
        echo '</div>';
        
        echo '</div>';
        
        echo '<p class="description">' . __('Faça upload de um ícone SVG ou PNG personalizado para o PIX na galeria de produtos. Se vazio, usa o mesmo ícone da página do produto.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo Margem para PIX - Página do Produto
     */
    public function pix_margin_product_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $margin = isset($settings['pix_margin_product']) ? $settings['pix_margin_product'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_design_settings[pix_margin_product]', $margin, 'Margem - Página do Produto');
    }
    
    /**
     * Callback do campo Preenchimento para PIX - Página do Produto
     */
    public function pix_padding_product_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $padding = isset($settings['pix_padding_product']) ? $settings['pix_padding_product'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_design_settings[pix_padding_product]', $padding, 'Preenchimento - Página do Produto');
    }
    
    /**
     * Callback do campo Margem para PIX - Galeria
     */
    public function pix_margin_gallery_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $margin = isset($settings['pix_margin_gallery']) ? $settings['pix_margin_gallery'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_design_settings[pix_margin_gallery]', $margin, 'Margem - Galeria de Produtos');
    }
    
    /**
     * Callback do campo Preenchimento para PIX - Galeria
     */
    public function pix_padding_gallery_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $padding = isset($settings['pix_padding_gallery']) ? $settings['pix_padding_gallery'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_design_settings[pix_padding_gallery]', $padding, 'Preenchimento - Galeria de Produtos');
    }
    
    /**
     * Callback do campo Raio da Borda para PIX
     */
    public function pix_border_radius_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $border_radius = isset($settings['pix_border_radius']) ? $settings['pix_border_radius'] : array('value' => '0', 'unit' => 'px');
        
        $this->render_border_radius_field('dw_pix_design_settings[pix_border_radius]', $border_radius);
    }
    
    /**
     * Renderiza campo de espaçamento (margin/padding)
     */
    private function render_spacing_field($field_name, $values, $label) {
        $top = isset($values['top']) ? $values['top'] : '0';
        $right = isset($values['right']) ? $values['right'] : '0';
        $bottom = isset($values['bottom']) ? $values['bottom'] : '0';
        $left = isset($values['left']) ? $values['left'] : '0';
        $unit = isset($values['unit']) ? $values['unit'] : 'px';
        
        // Determina se é margin (permite negativos) ou padding (não permite)
        $is_margin = (strpos($field_name, 'margin') !== false);
        $min_value = $is_margin ? '' : '0'; // Margin permite negativos, padding não
        
        echo '<div class="dw-spacing-field-container" style="margin-bottom: 20px;">';
        echo '<strong style="display: block; margin-bottom: 10px;">' . esc_html($label) . ':</strong>';
        echo '<div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">';
        
        // Top
        echo '<div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">';
        echo '<input type="number" name="' . esc_attr($field_name) . '[top]" value="' . esc_attr($top) . '"' . ($min_value !== '' ? ' min="' . esc_attr($min_value) . '"' : '') . ' step="0.1" style="width: 80px; padding: 5px;" />';
        echo '<label style="font-size: 11px; color: #666;">' . __('Superior', 'dw-price-to-pix') . '</label>';
        echo '</div>';
        
        // Right
        echo '<div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">';
        echo '<input type="number" name="' . esc_attr($field_name) . '[right]" value="' . esc_attr($right) . '"' . ($min_value !== '' ? ' min="' . esc_attr($min_value) . '"' : '') . ' step="0.1" style="width: 80px; padding: 5px;" />';
        echo '<label style="font-size: 11px; color: #666;">' . __('Direita', 'dw-price-to-pix') . '</label>';
        echo '</div>';
        
        // Bottom
        echo '<div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">';
        echo '<input type="number" name="' . esc_attr($field_name) . '[bottom]" value="' . esc_attr($bottom) . '"' . ($min_value !== '' ? ' min="' . esc_attr($min_value) . '"' : '') . ' step="0.1" style="width: 80px; padding: 5px;" />';
        echo '<label style="font-size: 11px; color: #666;">' . __('Inferior', 'dw-price-to-pix') . '</label>';
        echo '</div>';
        
        // Left
        echo '<div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">';
        echo '<input type="number" name="' . esc_attr($field_name) . '[left]" value="' . esc_attr($left) . '"' . ($min_value !== '' ? ' min="' . esc_attr($min_value) . '"' : '') . ' step="0.1" style="width: 80px; padding: 5px;" />';
        echo '<label style="font-size: 11px; color: #666;">' . __('Esquerda', 'dw-price-to-pix') . '</label>';
        echo '</div>';
        
        // Unit selector
        echo '<div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">';
        echo '<select name="' . esc_attr($field_name) . '[unit]" style="width: 80px; padding: 5px;">';
        echo '<option value="px"' . selected($unit, 'px', false) . '>PX</option>';
        echo '<option value="rem"' . selected($unit, 'rem', false) . '>REM</option>';
        echo '<option value="em"' . selected($unit, 'em', false) . '>EM</option>';
        echo '<option value="%"' . selected($unit, '%', false) . '>%</option>';
        echo '</select>';
        echo '<label style="font-size: 11px; color: #666;">' . __('Unidade', 'dw-price-to-pix') . '</label>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Callback do campo permitir fundo transparente para PIX
     */
    public function allow_transparent_background_pix_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['allow_transparent_background_pix']) ? $settings['allow_transparent_background_pix'] : '0';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_design_settings[allow_transparent_background_pix]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_design_settings[allow_transparent_background_pix]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Permitir fundo transparente (sem cor)', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando marcado, você pode usar "transparent" ou deixar vazio o campo de cor de fundo para não exibir cor.', 'dw-price-to-pix') . '</p>';
    }
    
    /**
     * Renderiza campo de raio da borda
     */
    private function render_border_radius_field($field_name, $values) {
        $value = isset($values['value']) ? $values['value'] : '0';
        $unit = isset($values['unit']) ? $values['unit'] : 'px';
        
        echo '<div class="dw-border-radius-field-container" style="margin-bottom: 20px;">';
        echo '<strong style="display: block; margin-bottom: 10px;">' . __('Raio da Borda:', 'dw-price-to-pix') . '</strong>';
        echo '<div style="display: flex; align-items: center; gap: 10px;">';
        
        echo '<input type="number" name="' . esc_attr($field_name) . '[value]" value="' . esc_attr($value) . '" min="0" step="0.1" style="width: 80px; padding: 5px;" />';
        
        echo '<select name="' . esc_attr($field_name) . '[unit]" style="width: 80px; padding: 5px;">';
        echo '<option value="px"' . selected($unit, 'px', false) . '>PX</option>';
        echo '<option value="rem"' . selected($unit, 'rem', false) . '>REM</option>';
        echo '<option value="em"' . selected($unit, 'em', false) . '>EM</option>';
        echo '<option value="%"' . selected($unit, '%', false) . '>%</option>';
        echo '</select>';
        
        echo '</div>';
        echo '</div>';
    }

    /**
     * Callback do campo texto personalizado
     */
    public function custom_text_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['custom_text']) ? $settings['custom_text'] : 'Pagando com PIX:';
        echo '<input type="text" name="dw_pix_design_settings[custom_text]" value="' . esc_attr($value) . '" style="width: 300px;" />';
        echo '<p class="description">' . __('Texto personalizado antes do preço (ex: "Pagando com PIX:", "Desconto PIX:")', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo estilo da borda
     */
    public function border_style_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['border_style']) ? $settings['border_style'] : 'solid';
        
        $options = array(
            'solid' => __('Sólida', 'dw-price-to-pix'),
            'dashed' => __('Tracejada', 'dw-price-to-pix'),
            'dotted' => __('Pontilhada', 'dw-price-to-pix'),
            'double' => __('Dupla', 'dw-price-to-pix'),
            'none' => __('Sem borda', 'dw-price-to-pix')
        );
        
        echo '<select name="dw_pix_design_settings[border_style]">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Estilo da borda esquerda do box', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo tamanho da fonte
     */
    public function font_size_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['font_size']) ? $settings['font_size'] : '16';
        
        echo '<select name="dw_pix_design_settings[font_size]">';
        for ($i = 12; $i <= 24; $i += 2) {
            echo '<option value="' . $i . '"' . selected($value, $i, false) . '>' . $i . 'px</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Tamanho da fonte do texto principal', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo texto de desconto
     */
    public function discount_text_callback() {
        $settings = get_option('dw_pix_design_settings', array());
        $value = isset($settings['discount_text']) ? $settings['discount_text'] : 'de desconto';
        echo '<input type="text" name="dw_pix_design_settings[discount_text]" value="' . esc_attr($value) . '" style="width: 300px;" />';
        echo '<p class="description">' . __('Texto que aparece após o percentual de desconto (ex: "de desconto", "OFF", "de economia")', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Página de configurações com abas
     */
    public function settings_page() {
        // Processa reset se solicitado
        // Processa exportação de configurações
        if (isset($_POST['dw_pix_export_settings']) && check_admin_referer('dw_pix_export_action', 'dw_pix_export_nonce')) {
            $this->export_settings();
            exit;
        }
        
        // Processa importação de configurações
        if (isset($_POST['dw_pix_import_settings']) && check_admin_referer('dw_pix_import_action', 'dw_pix_import_nonce')) {
            $result = $this->import_settings();
            if ($result['success']) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }
        
        if (isset($_POST['dw_pix_reset_settings']) && check_admin_referer('dw_pix_reset_action', 'dw_pix_reset_nonce')) {
            $this->reset_all_settings();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Configurações resetadas com sucesso!', 'dw-price-to-pix') . '</p></div>';
        }
        
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'pix';
        ?>
        <div class="wrap dw-pix-settings-wrap">
            <h1 class="dw-pix-title">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
            
            <p class="dw-pix-subtitle"><?php _e('Configure preços PIX e parcelas de cartão de crédito para seus produtos.', 'dw-price-to-pix'); ?></p>
            
            <!-- Navegação das Abas -->
            <nav class="nav-tab-wrapper dw-pix-nav-tabs">
                <a href="?page=dw-pix-settings&tab=pix" class="nav-tab <?php echo $active_tab == 'pix' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-money-alt"></span> <?php _e('PIX', 'dw-price-to-pix'); ?>
                </a>
                <a href="?page=dw-pix-settings&tab=parcelas" class="nav-tab <?php echo $active_tab == 'parcelas' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-cart"></span> <?php _e('Parcelas', 'dw-price-to-pix'); ?>
                </a>
                <a href="?page=dw-pix-settings&tab=design-pix" class="nav-tab <?php echo $active_tab == 'design-pix' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-art"></span> <?php _e('Design PIX', 'dw-price-to-pix'); ?>
                </a>
                <a href="?page=dw-pix-settings&tab=design-parcelas" class="nav-tab <?php echo $active_tab == 'design-parcelas' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-art"></span> <?php _e('Design Parcelas', 'dw-price-to-pix'); ?>
                </a>
                <a href="?page=dw-pix-settings&tab=avancado" class="nav-tab <?php echo $active_tab == 'avancado' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-tools"></span> <?php _e('Avançado', 'dw-price-to-pix'); ?>
                </a>
            </nav>
            
            <div class="dw-pix-admin-container">
                <?php
                // Exibe conteúdo baseado na aba ativa
                switch ($active_tab) {
                    case 'pix':
                        $this->render_pix_tab();
                        break;
                    case 'parcelas':
                        $this->render_parcelas_tab();
                        break;
                    case 'design-pix':
                        $this->render_design_pix_tab();
                        break;
                    case 'design-parcelas':
                        $this->render_design_parcelas_tab();
                        break;
                    case 'avancado':
                        $this->render_avancado_tab();
                        break;
                    default:
                        $this->render_pix_tab();
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza aba PIX
     */
    private function render_pix_tab() {
        ?>
        <div class="dw-pix-tab-content">
            <div class="dw-pix-admin-main">
                <form method="post" action="options.php">
                    <?php settings_fields('dw_pix_settings'); ?>
                    
                    <!-- Apenas configurações PIX -->
                    <table class="form-table" role="presentation">
                        <?php
                        // Exibe apenas campos da seção PIX
                        global $wp_settings_sections;
                        
                        if (isset($wp_settings_sections['dw_pix_settings']['dw_pix_global_section'])) {
                            echo '<tr><td colspan="2">';
                            echo '<h2>' . esc_html($wp_settings_sections['dw_pix_settings']['dw_pix_global_section']['title']) . '</h2>';
                            if ($wp_settings_sections['dw_pix_settings']['dw_pix_global_section']['callback']) {
                                call_user_func($wp_settings_sections['dw_pix_settings']['dw_pix_global_section']['callback']);
                            }
                            echo '</td></tr>';
                            
                            do_settings_fields('dw_pix_settings', 'dw_pix_global_section');
                        }
                        ?>
                    </table>
                    
                    <?php submit_button(__('Salvar Configurações do PIX', 'dw-price-to-pix'), 'primary large'); ?>
                </form>
            </div>
            
            <div class="dw-pix-admin-sidebar">
                <?php $this->render_pix_preview(); ?>
                <?php $this->render_info_box('pix'); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza aba Parcelas
     */
    private function render_parcelas_tab() {
        ?>
        <div class="dw-pix-tab-content">
                <div class="dw-pix-admin-main">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('dw_pix_settings');
                    ?>
                    
                    <table class="form-table" role="presentation">
                        <?php
                        // Exibe apenas campos da seção Parcelas
                        global $wp_settings_sections;
                        
                        if (isset($wp_settings_sections['dw_pix_settings']['dw_pix_installments_section'])) {
                            echo '<tr><td colspan="2">';
                            echo '<h2>' . esc_html($wp_settings_sections['dw_pix_settings']['dw_pix_installments_section']['title']) . '</h2>';
                            if ($wp_settings_sections['dw_pix_settings']['dw_pix_installments_section']['callback']) {
                                call_user_func($wp_settings_sections['dw_pix_settings']['dw_pix_installments_section']['callback']);
                            }
                            echo '</td></tr>';
                            
                            do_settings_fields('dw_pix_settings', 'dw_pix_installments_section');
                        }
                        ?>
                    </table>
                    
                    <?php submit_button(__('Salvar Configurações de Parcelas', 'dw-price-to-pix'), 'primary large'); ?>
                    </form>
                </div>
                
                <div class="dw-pix-admin-sidebar">
                <?php $this->render_parcelas_preview(); ?>
                <?php $this->render_info_box('parcelas'); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza aba Design PIX
     */
    private function render_design_pix_tab() {
        ?>
        <div class="dw-pix-tab-content">
            <div class="dw-pix-admin-main">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('dw_pix_settings');
                    ?>
                    
                    <!-- Design PIX -->
                    <table class="form-table" role="presentation">
                        <?php
                        global $wp_settings_sections;
                        
                        if (isset($wp_settings_sections['dw_pix_settings']['dw_pix_design_section'])) {
                            echo '<tr><td colspan="2">';
                            echo '<h2>' . esc_html($wp_settings_sections['dw_pix_settings']['dw_pix_design_section']['title']) . '</h2>';
                            if ($wp_settings_sections['dw_pix_settings']['dw_pix_design_section']['callback']) {
                                call_user_func($wp_settings_sections['dw_pix_settings']['dw_pix_design_section']['callback']);
                            }
                            echo '</td></tr>';
                            
                            do_settings_fields('dw_pix_settings', 'dw_pix_design_section');
                        }
                        ?>
                    </table>
                    
                    <?php submit_button(__('Salvar Configurações de Design PIX', 'dw-price-to-pix'), 'primary large'); ?>
                </form>
            </div>
            
            <div class="dw-pix-admin-sidebar">
                <?php $this->render_pix_preview(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Renderiza aba Design Parcelas
     */
    private function render_design_parcelas_tab() {
        ?>
        <div class="dw-pix-tab-content">
            <div class="dw-pix-admin-main">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('dw_pix_settings');
                    ?>
                    
                    <!-- Design Parcelas -->
                    <table class="form-table" role="presentation">
                        <?php
                        global $wp_settings_sections;
                        
                        if (isset($wp_settings_sections['dw_pix_settings']['dw_pix_installments_design_section'])) {
                            echo '<tr><td colspan="2">';
                            echo '<h2>' . esc_html($wp_settings_sections['dw_pix_settings']['dw_pix_installments_design_section']['title']) . '</h2>';
                            if ($wp_settings_sections['dw_pix_settings']['dw_pix_installments_design_section']['callback']) {
                                call_user_func($wp_settings_sections['dw_pix_settings']['dw_pix_installments_design_section']['callback']);
                            }
                            echo '</td></tr>';
                            
                            do_settings_fields('dw_pix_settings', 'dw_pix_installments_design_section');
                        }
                        ?>
                    </table>
                    
                    <?php submit_button(__('Salvar Configurações de Design Parcelas', 'dw-price-to-pix'), 'primary large'); ?>
                </form>
            </div>
            
            <div class="dw-pix-admin-sidebar">
                <?php $this->render_parcelas_preview(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza aba Avançado
     */
    private function render_avancado_tab() {
        ?>
        <div class="dw-pix-tab-content">
            <div class="dw-pix-admin-main-full">
                
                <!-- Exportar e Importar Configurações -->
                <div class="dw-pix-card">
                    <h2><span class="dashicons dashicons-download"></span> <?php _e('Exportar e Importar Configurações', 'dw-price-to-pix'); ?></h2>
                    <p class="description">
                        <?php _e('Exporte suas configurações para fazer backup ou importe configurações de outro site.', 'dw-price-to-pix'); ?>
                    </p>
                    
                    <div class="dw-pix-button-group" style="margin-top: 15px;">
                        <form method="post" action="" style="display: inline;">
                            <?php wp_nonce_field('dw_pix_export_action', 'dw_pix_export_nonce'); ?>
                            <input type="hidden" name="dw_pix_export_settings" value="1" />
                            <button type="submit" class="button button-secondary button-large">
                                <span class="dashicons dashicons-download"></span> <?php _e('Exportar Configurações', 'dw-price-to-pix'); ?>
                            </button>
                        </form>
                        
                        <form method="post" action="" enctype="multipart/form-data" id="dw-pix-import-form" style="display: inline;">
                            <?php wp_nonce_field('dw_pix_import_action', 'dw_pix_import_nonce'); ?>
                            <input type="file" name="dw_pix_import_file" accept=".json" style="display: none;" id="dw-pix-import-file" />
                            <input type="hidden" name="dw_pix_import_settings" value="1" />
                            <button type="button" class="button button-secondary button-large" id="dw-pix-import-button">
                                <span class="dashicons dashicons-upload"></span> <?php _e('Importar Configurações', 'dw-price-to-pix'); ?>
                            </button>
                        </form>
                    </div>
                    
                    <script>
                    (function() {
                        var importFile = document.getElementById('dw-pix-import-file');
                        var importButton = document.getElementById('dw-pix-import-button');
                        var importForm = document.getElementById('dw-pix-import-form');
                        
                        if (importButton && importFile && importForm) {
                            importButton.addEventListener('click', function() {
                                importFile.click();
                            });
                            
                            importFile.addEventListener('change', function() {
                                if (this.files.length > 0) {
                                    importForm.submit();
                                }
                            });
                        }
                    })();
                    </script>
                </div>
                
                <!-- Resetar Configurações -->
                <div class="dw-pix-card" style="margin-top: 20px;">
                    <h2><span class="dashicons dashicons-undo"></span> <?php _e('Resetar Configurações', 'dw-price-to-pix'); ?></h2>
                    <p class="description">
                        <?php _e('Restaura todas as configurações para os valores padrão. Esta ação não pode ser desfeita.', 'dw-price-to-pix'); ?>
                    </p>
                    
                    <form method="post" action="" onsubmit="return confirm('<?php _e('Tem certeza que deseja resetar TODAS as configurações? Esta ação não pode ser desfeita!', 'dw-price-to-pix'); ?>');">
                        <?php wp_nonce_field('dw_pix_reset_action', 'dw_pix_reset_nonce'); ?>
                        <input type="hidden" name="dw_pix_reset_settings" value="1" />
                        <button type="submit" class="button button-secondary button-large dw-pix-reset-button">
                            <span class="dashicons dashicons-undo"></span> <?php _e('Resetar Todas as Configurações', 'dw-price-to-pix'); ?>
                        </button>
                    </form>
                </div>
                
                <!-- Shortcodes -->
                <div class="dw-pix-card" style="margin-top: 20px;">
                    <h2><span class="dashicons dashicons-editor-code"></span> <?php _e('Shortcodes Disponíveis', 'dw-price-to-pix'); ?></h2>
                    
                    <h3><?php _e('Para Elementor ou Posicionamento Manual:', 'dw-price-to-pix'); ?></h3>
                    <p class="description">
                        <?php _e('Use estes shortcodes para exibir o PIX em qualquer lugar da página.', 'dw-price-to-pix'); ?>
                    </p>
                    
                    <table class="widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Shortcode', 'dw-price-to-pix'); ?></th>
                                <th><?php _e('Descrição', 'dw-price-to-pix'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>[dw_pix_price]</code></td>
                                <td><?php _e('Exibe o preço PIX do produto atual', 'dw-price-to-pix'); ?></td>
                            </tr>
                            <tr>
                                <td><code>[dw_pix_price product_id="123"]</code></td>
                                <td><?php _e('Exibe o preço PIX de um produto específico', 'dw-price-to-pix'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Informações do Sistema -->
                <div class="dw-pix-card" style="margin-top: 20px;">
                    <h2><span class="dashicons dashicons-info"></span> <?php _e('Informações do Sistema', 'dw-price-to-pix'); ?></h2>
                    
                    <table class="widefat fixed striped">
                        <tbody>
                            <tr>
                                <td><strong><?php _e('Versão do Plugin:', 'dw-price-to-pix'); ?></strong></td>
                                <td><?php echo DW_PARCELAS_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('WordPress:', 'dw-price-to-pix'); ?></strong></td>
                                <td><?php echo get_bloginfo('version'); ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('WooCommerce:', 'dw-price-to-pix'); ?></strong></td>
                                <td><?php echo defined('WC_VERSION') ? WC_VERSION : 'Não instalado'; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('PHP:', 'dw-price-to-pix'); ?></strong></td>
                                <td><?php echo PHP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('Elementor:', 'dw-price-to-pix'); ?></strong></td>
                                <td><?php echo defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : 'Não instalado'; ?></td>
                            </tr>
                            <tr>
                                <td><strong><?php _e('HPOS:', 'dw-price-to-pix'); ?></strong></td>
                                <td><?php echo class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') ? 'Compatível' : 'N/A'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Preview do PIX
     */
    private function render_pix_preview() {
        ?>
                    <div class="dw-pix-preview-box">
                        <h3><?php _e('Preview do Box PIX', 'dw-price-to-pix'); ?></h3>
                        <div id="dw-pix-preview" class="dw-pix-price-info">
                            <p class="dw-pix-price-text">
                    <strong>
                        <span class="pix-icon">
                            <img src="<?php echo esc_url(DW_PIX_PLUGIN_URL . 'assets/images/pix-svgrepo-com.svg'); ?>" alt="PIX" style="width: 20px; height: 20px; vertical-align: middle;" />
                        </span>
                        <span class="custom-text">Pagando com PIX:</span>
                    </strong>
                                <span class="dw-pix-price-amount">R$ 90,00</span>
                                <span class="dw-pix-discount-percent">(10% de desconto)</span>
                            </p>
                        </div>
                    </div>
        <?php
    }
                    
    /**
     * Preview das Parcelas
     */
    private function render_parcelas_preview() {
        ?>
                    <div class="dw-pix-preview-box" style="margin-top: 20px;">
                        <h3><?php _e('Preview do Box Cartão', 'dw-price-to-pix'); ?></h3>
                        <div id="dw-parcelas-preview" class="dw-parcelas-summary">
                <span class="dw-parcelas-icon">
                    <img src="<?php echo esc_url(DW_PIX_PLUGIN_URL . 'assets/images/credit-card.svg'); ?>" alt="Cartão" style="width: 20px; height: 20px; vertical-align: middle;" />
                </span>
                            <span class="dw-parcelas-text">R$ 1.000,00 em até 10x de R$ 100,00 sem juros</span>
                        </div>
                    </div>
        <?php
    }
                    
    /**
     * Box de informações
     */
    private function render_info_box($type = 'pix') {
        ?>
                    <div class="dw-pix-info-box">
                        <h3><?php _e('Informações', 'dw-price-to-pix'); ?></h3>
            <?php if ($type == 'pix'): ?>
                <p><?php _e('Configure o desconto global PIX ou defina preços individuais em cada produto.', 'dw-price-to-pix'); ?></p>
                <p><?php _e('Preços individuais têm prioridade sobre o desconto global.', 'dw-price-to-pix'); ?></p>
            <?php else: ?>
                <p><?php _e('As parcelas são calculadas automaticamente com base nas configurações.', 'dw-price-to-pix'); ?></p>
                <p><?php _e('Defina o máximo de parcelas, parcelas sem juros e taxa de juros.', 'dw-price-to-pix'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Sanitiza e mescla configurações globais PIX
     */
    public function sanitize_global_settings($input) {
        if (!is_array($input)) {
            return get_option('dw_pix_global_settings', array());
        }
        
        // Obtém valores existentes
        $existing = get_option('dw_pix_global_settings', array());
        
        // Mescla com novos valores
        $sanitized = wp_parse_args($input, $existing);
        
        // Sanitiza valores
        if (isset($sanitized['global_discount'])) {
            $sanitized['global_discount'] = sanitize_text_field($sanitized['global_discount']);
        }
        if (isset($sanitized['show_in_gallery'])) {
            $sanitized['show_in_gallery'] = sanitize_text_field($sanitized['show_in_gallery']);
        }
        if (isset($sanitized['pix_position'])) {
            $sanitized['pix_position'] = sanitize_text_field($sanitized['pix_position']);
        }
        
        return $sanitized;
    }

    /**
     * Sanitiza e mescla configurações de design PIX
     */
    public function sanitize_design_settings($input) {
        if (!is_array($input)) {
            return get_option('dw_pix_design_settings', array());
        }
        
        // Obtém valores existentes
        $existing = get_option('dw_pix_design_settings', array());
        
        // Mescla com novos valores
        $sanitized = wp_parse_args($input, $existing);
        
        // Sanitiza valores
        $allowed_keys = array('background_color', 'border_color', 'hide_border', 'text_color', 'price_color', 'pix_icon_custom', 'pix_icon_custom_gallery', 'show_pix_icon_gallery', 'custom_text', 'border_style', 'font_size', 'discount_text', 'pix_margin_product', 'pix_padding_product', 'pix_margin_gallery', 'pix_padding_gallery', 'pix_border_radius', 'allow_transparent_background_pix');
        foreach ($sanitized as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                if (in_array($key, array('pix_margin_product', 'pix_padding_product', 'pix_margin_gallery', 'pix_padding_gallery'))) {
                    // Arrays de espaçamento
                    $sanitized[$key] = $this->sanitize_spacing_array($value);
                } elseif ($key === 'pix_border_radius') {
                    // Array de border radius
                    $sanitized[$key] = $this->sanitize_border_radius_array($value);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitiza e mescla configurações de parcelas
     */
    public function sanitize_installments_settings($input) {
        if (!is_array($input)) {
            return get_option('dw_pix_installments_settings', array());
        }
        
        // Obtém valores existentes
        $existing = get_option('dw_pix_installments_settings', array());
        
        // Mescla com novos valores
        $sanitized = wp_parse_args($input, $existing);
        
        // Sanitiza valores
        $allowed_keys = array('installments_enabled', 'max_installments', 'installments_without_interest', 'interest_rate', 'min_installment_value', 'show_table', 'table_display_type', 'product_position', 'display_locations');
        foreach ($sanitized as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                if (is_array($value)) {
                    $sanitized[$key] = array_map('sanitize_text_field', $value);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitiza e mescla configurações de design de parcelas
     */
    public function sanitize_installments_design_settings($input) {
        if (!is_array($input)) {
            return get_option('dw_pix_installments_design_settings', array());
        }
        
        // Obtém valores existentes
        $existing = get_option('dw_pix_installments_design_settings', array());
        
        // Mescla com novos valores
        $sanitized = wp_parse_args($input, $existing);
        
        // Sanitiza valores
        $allowed_keys = array('background_color', 'border_color', 'text_color', 'icon_position', 'transparent_background', 'card_icon_custom', 'credit_card_icon_custom', 'credit_card_icon_custom_gallery', 'show_credit_card_icon_gallery', 'installments_border_style', 'installments_font_size', 'show_credit_card_icon', 'credit_card_icon_position', 'allow_transparent_background', 'installments_margin_product', 'installments_padding_product', 'installments_margin_gallery', 'installments_padding_gallery', 'installments_border_radius');
        foreach ($sanitized as $key => $value) {
            if (in_array($key, $allowed_keys)) {
                if (in_array($key, array('installments_margin_product', 'installments_padding_product', 'installments_margin_gallery', 'installments_padding_gallery'))) {
                    // Arrays de espaçamento
                    $sanitized[$key] = $this->sanitize_spacing_array($value);
                } elseif ($key === 'installments_border_radius') {
                    // Array de border radius
                    $sanitized[$key] = $this->sanitize_border_radius_array($value);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }
        
        return $sanitized;
    }

    /**
     * Reseta todas as configurações
     */
    /**
     * Exporta todas as configurações para um arquivo JSON
     */
    private function export_settings() {
        $settings = array(
            'version' => DW_PARCELAS_VERSION,
            'export_date' => current_time('mysql'),
            'dw_pix_global_settings' => get_option('dw_pix_global_settings', array()),
            'dw_pix_design_settings' => get_option('dw_pix_design_settings', array()),
            'dw_pix_installments_settings' => get_option('dw_pix_installments_settings', array()),
            'dw_pix_installments_design_settings' => get_option('dw_pix_installments_design_settings', array())
        );
        
        // Codifica para JSON com flags para garantir compatibilidade
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        // Verifica se houve erro na codificação
        if ($json === false) {
            wp_die(__('Erro ao gerar arquivo JSON de exportação.', 'dw-price-to-pix'), __('Erro de Exportação', 'dw-price-to-pix'), array('response' => 500));
        }
        
        $filename = 'dw-parcelas-pix-settings-' . date('Y-m-d-His') . '.json';
        
        // Limpa qualquer saída anterior
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $json;
        exit;
    }
    
    /**
     * Importa configurações de um arquivo JSON
     *
     * @return array Resultado da importação
     */
    private function import_settings() {
        if (!isset($_FILES['dw_pix_import_file']) || $_FILES['dw_pix_import_file']['error'] !== UPLOAD_ERR_OK) {
            return array(
                'success' => false,
                'message' => __('Erro ao fazer upload do arquivo. Por favor, tente novamente.', 'dw-price-to-pix')
            );
        }
        
        $file = $_FILES['dw_pix_import_file'];
        
        // Verifica se é um arquivo JSON
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'json') {
            return array(
                'success' => false,
                'message' => __('O arquivo deve ser um arquivo JSON válido.', 'dw-price-to-pix')
            );
        }
        
        // Lê o conteúdo do arquivo
        $file_content = file_get_contents($file['tmp_name']);
        
        // Remove BOM (Byte Order Mark) se existir
        $file_content = preg_replace('/^\xEF\xBB\xBF/', '', $file_content);
        
        // Remove espaços em branco no início e fim
        $file_content = trim($file_content);
        
        // Verifica se o arquivo não está vazio
        if (empty($file_content)) {
            return array(
                'success' => false,
                'message' => __('O arquivo está vazio ou não pôde ser lido.', 'dw-price-to-pix')
            );
        }
        
        // Tenta decodificar o JSON
        $settings = json_decode($file_content, true);
        $json_error = json_last_error();
        
        // Se houver erro, tenta corrigir problemas comuns
        if ($json_error !== JSON_ERROR_NONE) {
            // Tenta corrigir encoding UTF-8
            if ($json_error === JSON_ERROR_UTF8) {
                $file_content = mb_convert_encoding($file_content, 'UTF-8', mb_detect_encoding($file_content, 'UTF-8, ISO-8859-1, Windows-1252', true));
                $settings = json_decode($file_content, true);
                $json_error = json_last_error();
            }
            
            // Se ainda houver erro, retorna mensagem detalhada
            if ($json_error !== JSON_ERROR_NONE) {
                $error_messages = array(
                    JSON_ERROR_DEPTH => __('Profundidade máxima excedida.', 'dw-price-to-pix'),
                    JSON_ERROR_STATE_MISMATCH => __('JSON malformado ou inválido.', 'dw-price-to-pix'),
                    JSON_ERROR_CTRL_CHAR => __('Caractere de controle encontrado.', 'dw-price-to-pix'),
                    JSON_ERROR_SYNTAX => __('Erro de sintaxe no JSON.', 'dw-price-to-pix'),
                    JSON_ERROR_UTF8 => __('Caracteres UTF-8 inválidos.', 'dw-price-to-pix'),
                );
                
                $error_message = __('Erro ao ler o arquivo JSON. Arquivo inválido.', 'dw-price-to-pix');
                if (isset($error_messages[$json_error])) {
                    $error_message .= ' ' . $error_messages[$json_error];
                } else {
                    $error_message .= ' ' . sprintf(__('Erro JSON: %s', 'dw-price-to-pix'), json_last_error_msg());
                }
                
                return array(
                    'success' => false,
                    'message' => $error_message
                );
            }
        }
        
        // Verifica se o decode retornou null (arquivo inválido)
        if ($settings === null && $file_content !== 'null') {
            return array(
                'success' => false,
                'message' => __('Erro ao decodificar o arquivo JSON. O arquivo pode estar corrompido.', 'dw-price-to-pix')
            );
        }
        
        // Valida se tem as chaves necessárias (verifica se é um array válido)
        if (!is_array($settings)) {
            return array(
                'success' => false,
                'message' => __('Arquivo de configuração inválido. O arquivo deve conter um objeto JSON válido.', 'dw-price-to-pix')
            );
        }
        
        // Define valores padrão para chaves que podem não existir (compatibilidade com versões antigas)
        $required_keys = array(
            'dw_pix_global_settings' => array(),
            'dw_pix_design_settings' => array(),
            'dw_pix_installments_settings' => array(),
            'dw_pix_installments_design_settings' => array()
        );
        
        // Verifica e importa cada configuração
        foreach ($required_keys as $key => $default_value) {
            if (isset($settings[$key]) && is_array($settings[$key])) {
                update_option($key, $settings[$key]);
            } else {
                // Se não existir, usa valor padrão (não retorna erro, apenas usa padrão)
                if (!isset($settings[$key])) {
                    update_option($key, $default_value);
                }
            }
        }
        
        return array(
            'success' => true,
            'message' => __('Configurações importadas com sucesso!', 'dw-price-to-pix')
        );
    }
    
    /**
     * Reseta todas as configurações para os valores padrão
     */
    private function reset_all_settings() {
        delete_option('dw_pix_global_settings');
        delete_option('dw_pix_design_settings');
        delete_option('dw_pix_installments_settings');
        delete_option('dw_pix_installments_design_settings');
    }

    /**
     * Enfileira assets do admin
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'woocommerce_page_dw-pix-settings') {
            return;
        }

        wp_enqueue_style(
            'dw-pix-admin',
            DW_PIX_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            DW_PIX_VERSION
        );

        wp_enqueue_media(); // Necessário para upload de mídia
        
        wp_enqueue_script(
            'dw-pix-admin',
            DW_PIX_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            DW_PIX_VERSION,
            true
        );
    }

    /**
     * Obtém configurações globais
     */
    public static function get_global_settings() {
        $defaults = array(
            'global_discount' => '',
            'show_in_gallery' => '0',
            'pix_position' => 'after_installments'
        );

        $settings = get_option('dw_pix_global_settings', array());
        return wp_parse_args($settings, $defaults);
    }

    /**
     * Obtém configurações de design
     */
    public static function get_design_settings() {
        $default_icon_url = DW_PIX_PLUGIN_URL . 'assets/images/pix-svgrepo-com.svg';
        
        $defaults = array(
            'background_color' => '#e8f5e9',
            'border_color' => '#4caf50',
            'hide_border' => '0',
            'text_color' => '#2e7d32',
            'price_color' => '#1b5e20',
            'pix_icon_custom' => '',
            'show_pix_icon_gallery' => '1',
            'custom_text' => 'Pagando com PIX:',
            'border_style' => 'solid',
            'font_size' => '16',
            'discount_text' => 'de desconto',
            'allow_transparent_background_pix' => '0'
        );

        $settings = get_option('dw_pix_design_settings', array());
        $settings = wp_parse_args($settings, $defaults);
        
        // Se não tem ícone personalizado, usa o padrão
        if (empty($settings['pix_icon_custom'])) {
            $settings['pix_icon_custom'] = $default_icon_url;
        }
        
        return $settings;
    }

    /**
     * Obtém a URL do ícone PIX (personalizado ou padrão)
     *
     * @return string
     */
    public static function get_pix_icon_url() {
        $settings = self::get_design_settings();
        $default_icon_url = DW_PIX_PLUGIN_URL . 'assets/images/pix-svgrepo-com.svg';
        
        if (!empty($settings['pix_icon_custom'])) {
            return $settings['pix_icon_custom'];
        }
        
        return $default_icon_url;
    }

    /**
     * Obtém o tipo de ícone (texto ou imagem)
     *
     * @return string 'text' ou 'image'
     */
    public static function get_pix_icon_type() {
        // Sempre retorna 'image' (ícone padrão ou personalizado)
        return 'image';
    }

    /**
     * Callback da seção de configurações de parcelas
     */
    public function installments_section_callback() {
        echo '<p>' . __('Configure as opções de parcelamento para cartão de crédito.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo ativar/desativar parcelas
     */
    public function installments_enabled_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['enabled']) ? $settings['enabled'] : '0';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_installments_settings[enabled]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_settings[enabled]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Ativar exibição de parcelas de cartão de crédito', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando ativado, as parcelas serão exibidas na página do produto.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo máximo de parcelas
     */
    public function max_installments_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['max_installments']) ? $settings['max_installments'] : 12;
        
        echo '<input type="number" name="dw_pix_installments_settings[max_installments]" value="' . esc_attr($value) . '" min="1" max="24" style="width: 100px;" />';
        echo '<p class="description">' . __('Número máximo de parcelas que podem ser exibidas (padrão: 12).', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo parcelas sem juros
     */
    public function installments_without_interest_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['installments_without_interest']) ? $settings['installments_without_interest'] : 3;
        
        echo '<input type="number" name="dw_pix_installments_settings[installments_without_interest]" value="' . esc_attr($value) . '" min="1" max="24" style="width: 100px;" />';
        echo '<p class="description">' . __('Número de parcelas sem juros (ex: 3 = 3x sem juros, acima disso terá juros).', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo taxa de juros
     */
    public function interest_rate_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['interest_rate']) ? $settings['interest_rate'] : '2.99';
        
        echo '<input type="number" name="dw_pix_installments_settings[interest_rate]" value="' . esc_attr($value) . '" step="0.01" min="0" max="100" style="width: 100px;" />';
        echo '<span style="margin-left: 10px;">%</span>';
        echo '<p class="description">' . __('Taxa de juros mensal para parcelas acima do limite sem juros (ex: 2.99 = 2,99% ao mês).', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo parcela mínima
     */
    public function min_installment_value_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['min_installment_value']) ? $settings['min_installment_value'] : '5.00';
        
        echo '<input type="number" name="dw_pix_installments_settings[min_installment_value]" value="' . esc_attr($value) . '" step="0.01" min="0" style="width: 100px;" />';
        echo '<p class="description">' . __('Valor mínimo de cada parcela (ex: 5.00 = R$ 5,00). Se a divisão der menos que isso, reduz o número de parcelas.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo exibir tabela
     */
    public function show_table_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['show_table']) ? $settings['show_table'] : '1';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_installments_settings[show_table]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_settings[show_table]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Exibir tabela de parcelas', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando desmarcado, apenas o resumo das parcelas será exibido (sem tabela).', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo tipo de exibição da tabela
     */
    public function table_display_type_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['table_display_type']) ? $settings['table_display_type'] : 'accordion';
        
        $options = array(
            'accordion' => __('Sanfonada (Accordion)', 'dw-price-to-pix'),
            'popup' => __('Popup (Modal)', 'dw-price-to-pix'),
            'open' => __('Sempre Aberta', 'dw-price-to-pix')
        );
        
        echo '<select name="dw_pix_installments_settings[table_display_type]">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Escolha como a tabela de parcelas será exibida: Sanfonada (abre/fecha no lugar), Popup (abre em modal) ou Sempre Aberta.', 'dw-price-to-pix') . '</p>';
    }
    
    /**
     * Callback da seção de design das parcelas
     */
    public function installments_design_section_callback() {
        echo '<p>' . __('Personalize a aparência do resumo e tabela de parcelas conforme sua marca.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo cor de fundo do resumo
     */
    public function installments_background_color_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['background_color']) ? $settings['background_color'] : '#f5f5f5';
        $allow_transparent = isset($settings['allow_transparent_background']) && $settings['allow_transparent_background'] === '1';
        
        // Se valor está vazio ou transparente e permite transparente, usa valor padrão para o color picker
        $color_value = $value;
        if ($allow_transparent && (empty($value) || strtolower($value) === 'transparent')) {
            $color_value = '#f5f5f5'; // Valor padrão para o color picker
        }
        
        echo '<input type="color" name="dw_pix_installments_design_settings[background_color]" value="' . esc_attr($color_value) . '" id="dw-parcelas-background-color" />';
        echo '<input type="hidden" id="dw-parcelas-background-color-original" value="' . esc_attr($value) . '" />';
        
        if ($allow_transparent) {
            $is_transparent = (empty($value) || strtolower($value) === 'transparent');
            echo '<br><br>';
            echo '<label>';
            echo '<input type="checkbox" id="dw-parcelas-use-transparent" ' . checked($is_transparent, true, false) . ' />';
            echo ' ' . __('Usar fundo transparente', 'dw-price-to-pix');
            echo '</label>';
            echo '<script>
            jQuery(document).ready(function($) {
                var $colorInput = $("#dw-parcelas-background-color");
                var $transparentCheck = $("#dw-parcelas-use-transparent");
                var originalValue = $("#dw-parcelas-background-color-original").val();
                
                $transparentCheck.on("change", function() {
                    if ($(this).is(":checked")) {
                        $colorInput.val("transparent").prop("disabled", true);
                        // Cria campo hidden para salvar "transparent"
                        if ($("#dw-parcelas-bg-transparent-hidden").length === 0) {
                            $colorInput.after(\'<input type="hidden" id="dw-parcelas-bg-transparent-hidden" name="dw_pix_installments_design_settings[background_color]" value="transparent" />\');
                        }
                        $colorInput.attr("name", "");
                    } else {
                        $colorInput.prop("disabled", false).attr("name", "dw_pix_installments_design_settings[background_color]");
                        $("#dw-parcelas-bg-transparent-hidden").remove();
                        if (!$colorInput.val() || $colorInput.val() === "transparent") {
                            $colorInput.val("' . esc_js($color_value) . '");
                        }
                    }
                });
                
                // Inicializa estado
                if ($transparentCheck.is(":checked")) {
                    $colorInput.val("transparent").prop("disabled", true);
                    if ($("#dw-parcelas-bg-transparent-hidden").length === 0) {
                        $colorInput.after(\'<input type="hidden" id="dw-parcelas-bg-transparent-hidden" name="dw_pix_installments_design_settings[background_color]" value="transparent" />\');
                    }
                    $colorInput.attr("name", "");
                }
            });
            </script>';
        }
        
        echo '<p class="description">' . __('Cor de fundo do resumo de parcelas', 'dw-price-to-pix');
        if ($allow_transparent) {
            echo ' ' . __('(marque a opção acima para usar fundo transparente)', 'dw-price-to-pix');
        }
        echo '</p>';
    }

    /**
     * Callback do campo cor da borda
     */
    public function installments_border_color_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['border_color']) ? $settings['border_color'] : '#2c3e50';
        echo '<input type="color" name="dw_pix_installments_design_settings[border_color]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor da borda esquerda do resumo', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo cor do texto
     */
    public function installments_text_color_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['text_color']) ? $settings['text_color'] : '#333333';
        echo '<input type="color" name="dw_pix_installments_design_settings[text_color]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor do texto principal', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo cor do preço
     */
    public function installments_price_color_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['price_color']) ? $settings['price_color'] : '#2c3e50';
        echo '<input type="color" name="dw_pix_installments_design_settings[price_color]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . __('Cor dos valores de parcelas', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo estilo da borda
     */
    public function installments_border_style_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['border_style']) ? $settings['border_style'] : 'solid';
        
        $options = array(
            'solid' => __('Sólida', 'dw-price-to-pix'),
            'dashed' => __('Tracejada', 'dw-price-to-pix'),
            'dotted' => __('Pontilhada', 'dw-price-to-pix'),
            'double' => __('Dupla', 'dw-price-to-pix'),
            'none' => __('Sem borda', 'dw-price-to-pix')
        );
        
        echo '<select name="dw_pix_installments_design_settings[border_style]">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Estilo da borda esquerda do resumo', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo tamanho da fonte
     */
    public function installments_font_size_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['font_size']) ? $settings['font_size'] : '16';
        
        echo '<select name="dw_pix_installments_design_settings[font_size]">';
        for ($i = 12; $i <= 24; $i += 2) {
            echo '<option value="' . $i . '"' . selected($value, $i, false) . '>' . $i . 'px</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Tamanho da fonte do texto principal', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo texto antes das parcelas
     */
    public function text_before_installments_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['text_before_installments']) ? $settings['text_before_installments'] : '';
        echo '<input type="text" name="dw_pix_installments_settings[text_before_installments]" value="' . esc_attr($value) . '" style="width: 300px;" />';
        echo '<p class="description">' . __('Texto que aparece antes do valor parcelado (ex: "à partir de", "em até"). Deixe vazio para não exibir.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo texto após as parcelas
     */
    public function text_after_installments_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['text_after_installments']) ? $settings['text_after_installments'] : '';
        echo '<input type="text" name="dw_pix_installments_settings[text_after_installments]" value="' . esc_attr($value) . '" style="width: 300px;" />';
        echo '<p class="description">' . __('Texto que aparece após as parcelas (ex: "sem juros"). Deixe vazio para não exibir.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo visibilidade - onde exibir
     */
    public function display_locations_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['display_locations']) ? $settings['display_locations'] : array('product' => '1');
        
        if (empty($value) || !is_array($value)) {
            $value = array('product' => '1');
        }
        
        // Hidden fields para garantir que '0' seja enviado quando desmarcados
        echo '<input type="hidden" name="dw_pix_installments_settings[display_locations][product]" value="0" />';
        echo '<input type="hidden" name="dw_pix_installments_settings[display_locations][gallery]" value="0" />';
        echo '<input type="hidden" name="dw_pix_installments_settings[display_locations][cart]" value="0" />';
        echo '<input type="hidden" name="dw_pix_installments_settings[display_locations][checkout]" value="0" />';
        
        echo '<div style="display: flex; flex-direction: column; gap: 10px;">';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_settings[display_locations][product]" value="1" ' . checked(isset($value['product']) && $value['product'] === '1', true, false) . ' />';
        echo ' ' . __('Página do Produto', 'dw-price-to-pix');
        echo '</label>';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_settings[display_locations][gallery]" value="1" ' . checked(isset($value['gallery']) && $value['gallery'] === '1', true, false) . ' />';
        echo ' ' . __('Galeria de Produtos', 'dw-price-to-pix');
        echo '</label>';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_settings[display_locations][cart]" value="1" ' . checked(isset($value['cart']) && $value['cart'] === '1', true, false) . ' />';
        echo ' ' . __('Carrinho', 'dw-price-to-pix');
        echo '</label>';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_settings[display_locations][checkout]" value="1" ' . checked(isset($value['checkout']) && $value['checkout'] === '1', true, false) . ' />';
        echo ' ' . __('Checkout', 'dw-price-to-pix');
        echo '</label>';
        
        echo '</div>';
        echo '<p class="description">' . __('Selecione onde as parcelas devem ser exibidas.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo posição no produto único
     */
    public function product_position_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $value = isset($settings['product_position']) ? $settings['product_position'] : 'before_add_to_cart';
        
        $options = array(
            'before_price' => __('Antes do Preço', 'dw-price-to-pix'),
            'after_price' => __('Depois do Preço', 'dw-price-to-pix'),
            'before_add_to_cart' => __('Antes do Botão Comprar (Recomendado)', 'dw-price-to-pix'),
            'after_add_to_cart' => __('Depois do Botão Comprar', 'dw-price-to-pix') . ' ⚠️ ' . __('(Será ajustado para antes do botão)', 'dw-price-to-pix'),
            'before_meta' => __('Antes das Meta Informações', 'dw-price-to-pix') . ' ⚠️ ' . __('(Será ajustado para antes do botão)', 'dw-price-to-pix'),
            'after_meta' => __('Depois das Meta Informações', 'dw-price-to-pix') . ' ⚠️ ' . __('(Será ajustado para antes do botão)', 'dw-price-to-pix')
        );
        
        echo '<select name="dw_pix_installments_settings[product_position]">';
        foreach ($options as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Escolha a posição onde as parcelas devem aparecer na página do produto. <strong>Nota:</strong> Parcelas e PIX sempre aparecem acima do botão de comprar para melhor visualização.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo ícone do cartão
     */
    public function credit_card_icon_custom_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $icon_url = isset($settings['credit_card_icon_custom']) ? $settings['credit_card_icon_custom'] : '';
        $default_icon_url = DW_PIX_PLUGIN_URL . 'assets/images/credit-card.svg';
        
        // Se não tem ícone personalizado, usa o padrão
        if (empty($icon_url)) {
            $icon_url = $default_icon_url;
        }
        
        echo '<div class="dw-parcelas-icon-upload-container">';
        
        // Preview do ícone
        echo '<div class="dw-parcelas-icon-preview" style="margin-bottom: 10px;">';
        echo '<img id="dw-parcelas-credit-card-icon-preview" src="' . esc_url($icon_url) . '" alt="Ícone Cartão" data-default="' . esc_url($default_icon_url) . '" style="max-width: 48px; max-height: 48px; display: block; margin-bottom: 10px;" />';
        echo '</div>';
        
        // Campo hidden para URL do ícone
        echo '<input type="hidden" id="dw-parcelas-credit-card-icon-url" name="dw_pix_installments_design_settings[credit_card_icon_custom]" value="' . esc_attr($settings['credit_card_icon_custom'] ?? '') . '" />';
        
        // Botões
        echo '<div style="display: flex; gap: 10px; align-items: center;">';
        echo '<button type="button" class="button" id="dw-parcelas-upload-credit-card-icon">' . __('Escolher Ícone', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-parcelas-remove-credit-card-icon" style="' . (empty($settings['credit_card_icon_custom']) ? 'display: none;' : '') . '">' . __('Remover', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-parcelas-reset-credit-card-icon">' . __('Usar Padrão', 'dw-price-to-pix') . '</button>';
        echo '</div>';
        
        echo '</div>';
        
        echo '<p class="description">' . __('Faça upload de um ícone SVG ou PNG personalizado para o cartão de crédito na página do produto. Tamanho recomendado: 48x48px ou maior.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo exibir ícone do cartão na galeria
     */
    public function show_credit_card_icon_gallery_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['show_credit_card_icon_gallery']) ? $settings['show_credit_card_icon_gallery'] : '1';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_installments_design_settings[show_credit_card_icon_gallery]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_design_settings[show_credit_card_icon_gallery]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Exibir ícone do cartão na galeria de produtos', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando ativado, o ícone do cartão de crédito será exibido junto com as parcelas na galeria de produtos.', 'dw-price-to-pix') . '</p>';
    }
    
    /**
     * Callback do campo ícone do cartão para galeria
     */
    public function credit_card_icon_custom_gallery_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $icon_url = isset($settings['credit_card_icon_custom_gallery']) ? $settings['credit_card_icon_custom_gallery'] : '';
        $default_icon_url = DW_PIX_PLUGIN_URL . 'assets/images/credit-card.svg';
        
        // Se não tem ícone personalizado, usa o padrão
        if (empty($icon_url)) {
            $icon_url = $default_icon_url;
        }
        
        echo '<div class="dw-parcelas-icon-upload-container">';
        
        // Preview do ícone
        echo '<div class="dw-pix-icon-preview" style="margin-bottom: 10px;">';
        echo '<img id="dw-parcelas-credit-card-icon-gallery-preview" src="' . esc_url($icon_url) . '" alt="Ícone Cartão Galeria" data-default="' . esc_url($default_icon_url) . '" style="max-width: 48px; max-height: 48px; display: block; margin-bottom: 10px;" />';
        echo '</div>';
        
        echo '<input type="hidden" id="dw-parcelas-credit-card-icon-gallery-url" name="dw_pix_installments_design_settings[credit_card_icon_custom_gallery]" value="' . esc_attr($settings['credit_card_icon_custom_gallery'] ?? '') . '" />';
        
        // Botões
        echo '<div style="display: flex; gap: 10px; align-items: center;">';
        echo '<button type="button" class="button" id="dw-parcelas-upload-credit-card-icon-gallery">' . __('Escolher Ícone', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-parcelas-remove-credit-card-icon-gallery" style="' . (empty($settings['credit_card_icon_custom_gallery']) ? 'display: none;' : '') . '">' . __('Remover', 'dw-price-to-pix') . '</button>';
        echo '<button type="button" class="button" id="dw-parcelas-reset-credit-card-icon-gallery">' . __('Usar Padrão', 'dw-price-to-pix') . '</button>';
        echo '</div>';
        
        echo '</div>';
        
        echo '<p class="description">' . __('Faça upload de um ícone SVG ou PNG personalizado para o cartão na galeria de produtos. Se vazio, usa o mesmo ícone da página do produto.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo Margem para Parcelas - Página do Produto
     */
    public function installments_margin_product_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $margin = isset($settings['installments_margin_product']) ? $settings['installments_margin_product'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_installments_design_settings[installments_margin_product]', $margin, 'Margem - Página do Produto');
    }
    
    /**
     * Callback do campo Preenchimento para Parcelas - Página do Produto
     */
    public function installments_padding_product_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $padding = isset($settings['installments_padding_product']) ? $settings['installments_padding_product'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_installments_design_settings[installments_padding_product]', $padding, 'Preenchimento - Página do Produto');
    }
    
    /**
     * Callback do campo Margem para Parcelas - Galeria
     */
    public function installments_margin_gallery_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $margin = isset($settings['installments_margin_gallery']) ? $settings['installments_margin_gallery'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_installments_design_settings[installments_margin_gallery]', $margin, 'Margem - Galeria de Produtos');
    }
    
    /**
     * Callback do campo Preenchimento para Parcelas - Galeria
     */
    public function installments_padding_gallery_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $padding = isset($settings['installments_padding_gallery']) ? $settings['installments_padding_gallery'] : array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        
        $this->render_spacing_field('dw_pix_installments_design_settings[installments_padding_gallery]', $padding, 'Preenchimento - Galeria de Produtos');
    }
    
    /**
     * Callback do campo Raio da Borda para Parcelas
     */
    public function installments_border_radius_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $border_radius = isset($settings['installments_border_radius']) ? $settings['installments_border_radius'] : array('value' => '0', 'unit' => 'px');
        
        $this->render_border_radius_field('dw_pix_installments_design_settings[installments_border_radius]', $border_radius);
    }

    /**
     * Sanitiza array de espaçamento
     */
    private function sanitize_spacing_array($value) {
        if (!is_array($value)) {
            return array('top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'unit' => 'px');
        }
        
        $sanitized = array();
        $sanitized['top'] = isset($value['top']) ? sanitize_text_field($value['top']) : '0';
        $sanitized['right'] = isset($value['right']) ? sanitize_text_field($value['right']) : '0';
        $sanitized['bottom'] = isset($value['bottom']) ? sanitize_text_field($value['bottom']) : '0';
        $sanitized['left'] = isset($value['left']) ? sanitize_text_field($value['left']) : '0';
        $sanitized['unit'] = isset($value['unit']) && in_array($value['unit'], array('px', 'rem', 'em', '%')) ? $value['unit'] : 'px';
        
        return $sanitized;
    }
    
    /**
     * Sanitiza array de border radius
     */
    private function sanitize_border_radius_array($value) {
        if (!is_array($value)) {
            return array('value' => '0', 'unit' => 'px');
        }
        
        $sanitized = array();
        $sanitized['value'] = isset($value['value']) ? sanitize_text_field($value['value']) : '0';
        $sanitized['unit'] = isset($value['unit']) && in_array($value['unit'], array('px', 'rem', 'em', '%')) ? $value['unit'] : 'px';
        
        return $sanitized;
    }
    
    /**
     * Gera CSS a partir dos valores de espaçamento
     */
    public static function generate_spacing_css($spacing_array, $property = 'margin') {
        if (!is_array($spacing_array) || empty($spacing_array)) {
            return '';
        }
        
        // Converte valores vazios ou inválidos para 0
        $top = isset($spacing_array['top']) && $spacing_array['top'] !== '' ? floatval($spacing_array['top']) : 0;
        $right = isset($spacing_array['right']) && $spacing_array['right'] !== '' ? floatval($spacing_array['right']) : 0;
        $bottom = isset($spacing_array['bottom']) && $spacing_array['bottom'] !== '' ? floatval($spacing_array['bottom']) : 0;
        $left = isset($spacing_array['left']) && $spacing_array['left'] !== '' ? floatval($spacing_array['left']) : 0;
        $unit = isset($spacing_array['unit']) && in_array($spacing_array['unit'], array('px', 'rem', 'em', '%')) ? $spacing_array['unit'] : 'px';
        
        // Para padding, não permite valores negativos
        if ($property === 'padding') {
            $top = max(0, $top);
            $right = max(0, $right);
            $bottom = max(0, $bottom);
            $left = max(0, $left);
            
            // Se todos são 0, não gera CSS
            if ($top == 0 && $right == 0 && $bottom == 0 && $left == 0) {
                return '';
            }
        } else {
            // Para margin, permite valores negativos
            // Se todos são 0, não gera CSS
            if ($top == 0 && $right == 0 && $bottom == 0 && $left == 0) {
                return '';
            }
        }
        
        // Se todos são iguais, usa shorthand
        if ($top == $right && $right == $bottom && $bottom == $left) {
            return $property . ': ' . $top . $unit . ';';
        }
        
        // Se top/bottom e left/right são iguais
        if ($top == $bottom && $left == $right) {
            return $property . ': ' . $top . $unit . ' ' . $left . $unit . ';';
        }
        
        // Caso completo
        return $property . ': ' . $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit . ';';
    }
    
    /**
     * Gera CSS para border-radius
     */
    public static function generate_border_radius_css($border_radius_array) {
        if (!is_array($border_radius_array) || empty($border_radius_array)) {
            return '';
        }
        
        // Converte valor vazio ou inválido para 0
        $value = isset($border_radius_array['value']) && $border_radius_array['value'] !== '' ? floatval($border_radius_array['value']) : 0;
        $unit = isset($border_radius_array['unit']) && in_array($border_radius_array['unit'], array('px', 'rem', 'em', '%')) ? $border_radius_array['unit'] : 'px';
        
        // Se valor é 0 ou negativo, não gera CSS
        if ($value <= 0) {
            return '';
        }
        
        // Garante que valor negativo não seja usado
        $value = max(0, $value);
        
        return 'border-radius: ' . $value . $unit . ';';
    }

    /**
     * Callback do campo exibir ícone do cartão
     */
    public function show_credit_card_icon_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['show_credit_card_icon']) ? $settings['show_credit_card_icon'] : '1';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_installments_design_settings[show_credit_card_icon]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_design_settings[show_credit_card_icon]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Exibir ícone do cartão de crédito', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando desmarcado, o ícone do cartão não será exibido.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo posição do ícone
     */
    public function credit_card_icon_position_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['credit_card_icon_position']) ? $settings['credit_card_icon_position'] : 'before';
        
        echo '<select name="dw_pix_installments_design_settings[credit_card_icon_position]">';
        echo '<option value="before"' . selected($value, 'before', false) . '>' . __('Antes do Texto', 'dw-price-to-pix') . '</option>';
        echo '<option value="after"' . selected($value, 'after', false) . '>' . __('Depois do Texto', 'dw-price-to-pix') . '</option>';
        echo '<option value="none"' . selected($value, 'none', false) . '>' . __('Não Exibir', 'dw-price-to-pix') . '</option>';
        echo '</select>';
        echo '<p class="description">' . __('Escolha onde o ícone do cartão deve aparecer em relação ao texto.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo permitir fundo transparente
     */
    public function allow_transparent_background_callback() {
        $settings = get_option('dw_pix_installments_design_settings', array());
        $value = isset($settings['allow_transparent_background']) ? $settings['allow_transparent_background'] : '0';
        
        // Hidden field para garantir que '0' seja enviado quando desmarcado
        echo '<input type="hidden" name="dw_pix_installments_design_settings[allow_transparent_background]" value="0" />';
        
        echo '<label>';
        echo '<input type="checkbox" name="dw_pix_installments_design_settings[allow_transparent_background]" value="1" ' . checked($value, '1', false) . ' />';
        echo ' ' . __('Permitir fundo transparente (sem cor)', 'dw-price-to-pix');
        echo '</label>';
        echo '<p class="description">' . __('Quando marcado, você pode usar "transparent" ou deixar vazio o campo de cor de fundo para não exibir cor.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Callback do campo textos por localização
     */
    public function location_texts_callback() {
        $settings = get_option('dw_pix_installments_settings', array());
        $location_texts = isset($settings['location_texts']) ? $settings['location_texts'] : array();
        
        if (!is_array($location_texts)) {
            $location_texts = array();
        }
        
        $locations = array(
            'product' => __('Página do Produto', 'dw-price-to-pix'),
            'gallery' => __('Galeria de Produtos', 'dw-price-to-pix'),
            'cart' => __('Carrinho', 'dw-price-to-pix'),
            'checkout' => __('Checkout', 'dw-price-to-pix')
        );
        
        echo '<div style="display: flex; flex-direction: column; gap: 15px;">';
        
        foreach ($locations as $key => $label) {
            $text_before = isset($location_texts[$key]['text_before']) ? $location_texts[$key]['text_before'] : '';
            $text_after = isset($location_texts[$key]['text_after']) ? $location_texts[$key]['text_after'] : '';
            
            echo '<div style="border: 1px solid #ddd; padding: 15px; border-radius: 4px;">';
            echo '<h4 style="margin-top: 0;">' . esc_html($label) . '</h4>';
            
            echo '<p>';
            echo '<label style="display: block; margin-bottom: 5px;"><strong>' . __('Texto Antes:', 'dw-price-to-pix') . '</strong></label>';
            echo '<input type="text" name="dw_pix_installments_settings[location_texts][' . esc_attr($key) . '][text_before]" value="' . esc_attr($text_before) . '" style="width: 100%;" />';
            echo '<span class="description">' . __('Deixe vazio para usar o texto padrão global.', 'dw-price-to-pix') . '</span>';
            echo '</p>';
            
            echo '<p>';
            echo '<label style="display: block; margin-bottom: 5px;"><strong>' . __('Texto Depois:', 'dw-price-to-pix') . '</strong></label>';
            echo '<input type="text" name="dw_pix_installments_settings[location_texts][' . esc_attr($key) . '][text_after]" value="' . esc_attr($text_after) . '" style="width: 100%;" />';
            echo '<span class="description">' . __('Deixe vazio para usar o texto padrão global.', 'dw-price-to-pix') . '</span>';
            echo '</p>';
            
            echo '</div>';
        }
        
        echo '</div>';
        echo '<p class="description">' . __('Configure textos personalizados para cada localização. Se deixar vazio, usará os textos globais configurados acima.', 'dw-price-to-pix') . '</p>';
    }

    /**
     * Obtém configurações de parcelas
     *
     * @return array
     */
    public static function get_installments_settings() {
        $defaults = array(
            'enabled' => '0',
            'max_installments' => 12,
            'installments_without_interest' => 3,
            'interest_rate' => '2.99',
            'min_installment_value' => 5.00,
            'table_display_type' => 'accordion',
            'show_table' => '1',
            'text_before_installments' => '',
            'text_after_installments' => '',
            'display_locations' => array('product' => '1'),
            'product_position' => 'after_price',
            'location_texts' => array()
        );

        $settings = get_option('dw_pix_installments_settings', array());
        $merged = wp_parse_args($settings, $defaults);
        
        // Garante que display_locations seja array
        if (!is_array($merged['display_locations'])) {
            $merged['display_locations'] = $defaults['display_locations'];
        }
        
        // Garante que location_texts seja array
        if (!is_array($merged['location_texts'])) {
            $merged['location_texts'] = $defaults['location_texts'];
        }
        
        return $merged;
    }

    /**
     * Obtém configurações de design das parcelas
     *
     * @return array
     */
    public static function get_installments_design_settings() {
        $default_icon_url = DW_PIX_PLUGIN_URL . 'assets/images/credit-card.svg';
        
        $defaults = array(
            'background_color' => '#f5f5f5',
            'border_color' => '#2c3e50',
            'text_color' => '#333333',
            'price_color' => '#2c3e50',
            'border_style' => 'solid',
            'font_size' => '16',
            'credit_card_icon_custom' => '',
            'show_credit_card_icon' => '1',
            'show_credit_card_icon_gallery' => '1',
            'credit_card_icon_position' => 'before',
            'allow_transparent_background' => '0'
        );

        $settings = get_option('dw_pix_installments_design_settings', array());
        $settings = wp_parse_args($settings, $defaults);
        
        // Se não tem ícone personalizado, usa o padrão para exibição
        if (empty($settings['credit_card_icon_custom'])) {
            $settings['credit_card_icon_custom'] = $default_icon_url;
        }
        
        return $settings;
    }
}
