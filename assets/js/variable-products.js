/**
 * JavaScript para produtos variáveis - DW Price to PIX
 *
 * @package DW_Price_To_Pix
 */

(function($) {
    'use strict';

    // Classe para gerenciar preços PIX de produtos variáveis
    var VariablePixPrice = {
        
        // Configurações
        config: {
            pixPrices: {},
            regularPrices: {},
            container: null,
            updateInterval: 1000
        },

        // Inicializa o sistema
        init: function(pixPrices, regularPrices) {
            this.config.pixPrices = pixPrices || {};
            this.config.regularPrices = regularPrices || {};
            
            this.setupContainer();
            this.bindEvents();
            this.startPeriodicUpdate();
            
            // Atualiza inicialmente
            setTimeout(this.updatePixPrice.bind(this), 500);
        },

        // Configura o container para exibir preço PIX
        setupContainer: function() {
            var $container = $('.dw-pix-variation-price');
            
            if ($container.length === 0) {
                $container = $('.dw-pix-price-info');
                if ($container.length === 0) {
                    // Tenta diferentes seletores para encontrar o local correto
                    var $target = $('.woocommerce-variation-price');
                    if ($target.length === 0) {
                        $target = $('.woocommerce-Price-amount').closest('.woocommerce-variation-price');
                    }
                    if ($target.length === 0) {
                        $target = $('.price');
                    }
                    
                    $target.after('<div class="dw-pix-price-info" style="display: none;"></div>');
                    $container = $('.dw-pix-price-info');
                }
            }
            
            this.config.container = $container;
        },

        // Vincula eventos
        bindEvents: function() {
            var self = this;
            
            // Eventos do WooCommerce
            $('form.variations_form').on('woocommerce_variation_has_changed', function() {
                setTimeout(self.updatePixPrice.bind(self), 100);
            });
            
            $('form.variations_form').on('woocommerce_variation_price_updated', function() {
                setTimeout(self.updatePixPrice.bind(self), 100);
            });
            
            $('form.variations_form').on('found_variation', function(event, variation) {
                setTimeout(self.updatePixPrice.bind(self), 100);
            });
            
            // Eventos genéricos para diferentes temas
            $('form.variations_form').on('change', 'select, input, .variation-select', function() {
                setTimeout(self.updatePixPrice.bind(self), 200);
            });
            
            // Eventos específicos para alguns temas
            $(document).on('change', '.variation-select, .variation-selector', function() {
                setTimeout(self.updatePixPrice.bind(self), 200);
            });
        },

        // Inicia atualização periódica
        startPeriodicUpdate: function() {
            var self = this;
            
            setInterval(function() {
                if ($('input[name="variation_id"]').val() || $('.variation-select').val()) {
                    self.updatePixPrice();
                }
            }, this.config.updateInterval);
        },

        // Atualiza o preço PIX
        updatePixPrice: function() {
            var selectedVariation = this.getSelectedVariation();
            
            if (!selectedVariation || !this.config.pixPrices[selectedVariation]) {
                this.hidePixPrice();
                return;
            }
            
            var pixPrice = this.config.pixPrices[selectedVariation];
            var regularPrice = this.getRegularPrice(selectedVariation);
            
            if (regularPrice > 0 && pixPrice < regularPrice) {
                this.showPixPrice(pixPrice, regularPrice);
            } else {
                this.hidePixPrice();
            }
        },

        // Obtém a variação selecionada
        getSelectedVariation: function() {
            var variationId = $('input[name="variation_id"]').val();
            
            if (!variationId) {
                // Tenta outros seletores
                variationId = $('.variation-select').val();
            }
            
            if (!variationId) {
                // Tenta pegar do atributo data
                variationId = $('.variation-selector:checked').attr('data-variation-id');
            }
            
            return variationId;
        },

        // Obtém o preço regular
        getRegularPrice: function(variationId) {
            var regularPrice = this.config.regularPrices[variationId] || 0;
            
            if (regularPrice <= 0) {
                // Tenta pegar do elemento da página
                var $priceElement = $('.woocommerce-Price-amount').first();
                if ($priceElement.length) {
                    var priceText = $priceElement.text();
                    regularPrice = parseFloat(priceText.replace(/[^\d,]/g, '').replace(',', '.'));
                }
            }
            
            return regularPrice;
        },

        // Exibe o preço PIX
        showPixPrice: function(pixPrice, regularPrice) {
            var discountAmount = regularPrice - pixPrice;
            var discountPercent = Math.round((discountAmount / regularPrice) * 100);
            
            // Obtém configurações de design
            var settings = this.getDesignSettings();
            
            // Obtém HTML do ícone
            var iconHtml = this.getIconHtml(settings);
            
            var pixHtml = '<div class="dw-pix-price-info" style="' + this.generateContainerStyles(settings) + '">';
            pixHtml += '<p class="dw-pix-price-text" style="' + this.generateTextStyles(settings) + '">';
            pixHtml += '<span class="pix-icon">' + iconHtml + '</span> ' + settings.custom_text + ' ';
            pixHtml += '<span class="dw-pix-price-amount" style="' + this.generatePriceStyles(settings) + '">R$ ' + pixPrice.toFixed(2).replace('.', ',') + '</span>';
            pixHtml += '<span class="dw-pix-discount-percent">(' + discountPercent + '% ' + settings.discount_text + ')</span>';
            pixHtml += '</p>';
            pixHtml += '</div>';
            
            this.config.container.html(pixHtml);
            this.config.container.show();
        },

        // Obtém configurações de design
        getDesignSettings: function() {
            // Tenta obter do elemento data ou usa padrões
            var $settingsElement = $('[data-dw-pix-settings]');
            if ($settingsElement.length) {
                try {
                    return JSON.parse($settingsElement.attr('data-dw-pix-settings'));
                } catch (e) {
                    console.log('Erro ao parsear configurações PIX:', e);
                }
            }
            
            // Configurações padrão
            var defaultIconUrl = '';
            var $settingsElement = $('[data-dw-pix-settings]');
            if ($settingsElement.length) {
                try {
                    var parsedSettings = JSON.parse($settingsElement.attr('data-dw-pix-settings'));
                    defaultIconUrl = parsedSettings.pix_icon_custom || '';
                } catch (e) {
                    console.log('Erro ao parsear configurações PIX:', e);
                }
            }
            
            return {
                background_color: '#e8f5e9',
                border_color: '#4caf50',
                text_color: '#2e7d32',
                price_color: '#1b5e20',
                pix_icon_custom: defaultIconUrl,
                custom_text: 'Pagando com PIX:',
                border_style: 'solid',
                font_size: '16',
                discount_text: 'de desconto'
            };
        },

        // Obtém HTML do ícone (texto ou imagem)
        getIconHtml: function(settings) {
            // Sempre usa ícone de imagem (personalizado ou padrão)
            var iconUrl = settings.pix_icon_custom || '';
            if (iconUrl) {
                return '<img src="' + iconUrl + '" alt="PIX" style="width: 20px; height: 20px; vertical-align: middle; display: inline-block;" />';
            }
            
            return '';
        },

        // Gera estilos do container
        generateContainerStyles: function(settings) {
            return 'background-color: ' + settings.background_color + '; ' +
                   'border-left: 4px ' + settings.border_style + ' ' + settings.border_color + ';';
        },

        // Gera estilos do texto
        generateTextStyles: function(settings) {
            return 'color: ' + settings.text_color + '; ' +
                   'font-size: ' + settings.font_size + 'px;';
        },

        // Gera estilos do preço
        generatePriceStyles: function(settings) {
            var fontSize = settings.font_size || '16';
            return 'color: ' + settings.price_color + '; font-size: ' + fontSize + 'px;';
        },

        // Oculta o preço PIX
        hidePixPrice: function() {
            this.config.container.hide();
        }
    };

    // Expõe a classe globalmente
    window.DWVariablePixPrice = VariablePixPrice;

})(jQuery);
