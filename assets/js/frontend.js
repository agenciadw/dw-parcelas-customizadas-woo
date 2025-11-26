/**
 * JavaScript para frontend - Parcelas e PIX
 * @package DW_Parcelas_Pix_WooCommerce
 */

(function($) {
    'use strict';

    // Classe para gerenciar parcelas de produtos variáveis
    var VariableInstallments = {
        
        // Configurações
        config: {
            variationPrices: {},
            settings: {},
            designSettings: {},
            strings: {},
            container: null
        },

        // Inicializa o sistema
        init: function() {
            if (typeof window.dwParcelasData === 'undefined') {
                return;
            }
            
            this.config.variationPrices = window.dwParcelasData.variationPrices || {};
            this.config.settings = window.dwParcelasData.settings || {};
            this.config.designSettings = window.dwParcelasData.designSettings || {};
            this.config.strings = window.dwParcelasData.strings || {};
            
            this.setupContainer();
            this.bindEvents();
            
            // Atualiza inicialmente
            setTimeout(this.updateInstallments.bind(this), 500);
        },

        // Configura o container para exibir parcelas
        setupContainer: function() {
            var $container = $('.dw-parcelas-variation-container');
            
            if ($container.length === 0) {
                // Cria container se não existir
                var $target = $('.woocommerce-variation-price');
                if ($target.length === 0) {
                    $target = $('.price').last();
                }
                
                $target.after('<div class="dw-parcelas-variation-container"></div>');
                $container = $('.dw-parcelas-variation-container');
            }
            
            this.config.container = $container;
        },

        // Vincula eventos
        bindEvents: function() {
            var self = this;
            
            // Eventos do WooCommerce para variações
            $('form.variations_form').on('woocommerce_variation_has_changed', function() {
                setTimeout(self.updateInstallments.bind(self), 100);
            });
            
            $('form.variations_form').on('woocommerce_variation_price_updated', function() {
                setTimeout(self.updateInstallments.bind(self), 100);
            });
            
            $('form.variations_form').on('found_variation', function(event, variation) {
                setTimeout(self.updateInstallments.bind(self), 100);
            });
            
            // Eventos genéricos para diferentes temas
            $('form.variations_form').on('change', 'select, input, .variation-select', function() {
                setTimeout(self.updateInstallments.bind(self), 200);
            });
        },

        // Atualiza as parcelas
        updateInstallments: function() {
            var selectedVariation = this.getSelectedVariation();
            
            if (!selectedVariation || !this.config.variationPrices[selectedVariation]) {
                this.hideInstallments();
                return;
            }
            
            var price = this.config.variationPrices[selectedVariation];
            
            if (price > 0) {
                this.showInstallments(price);
            } else {
                this.hideInstallments();
            }
        },

        // Obtém a variação selecionada
        getSelectedVariation: function() {
            var variationId = $('input[name="variation_id"]').val();
            
            if (!variationId) {
                variationId = $('.variation-select').val();
            }
            
            if (!variationId) {
                variationId = $('.variation-selector:checked').attr('data-variation-id');
            }
            
            return variationId;
        },

        // Exibe as parcelas
        showInstallments: function(price) {
            price = parseFloat(price);
            
            if (price <= 0 || !this.config.settings) {
                this.hideInstallments();
                return;
            }
            
            var installments = this.calculateInstallments(price);
            
            if (installments.length === 0) {
                this.hideInstallments();
                return;
            }
            
            var html = this.generateInstallmentsHTML(price, installments);
            this.config.container.html(html).show();
            
            // Reinicializa controles (accordion/popup)
            ParcelasAccordion.init();
        },

        // Calcula as parcelas
        calculateInstallments: function(price) {
            var settings = this.config.settings;
            var maxInstallments = parseInt(settings.max_installments) || 12;
            var installmentsWithoutInterest = parseInt(settings.installments_without_interest) || 3;
            var interestRate = parseFloat(settings.interest_rate) || 2.99;
            var minInstallmentValue = parseFloat(settings.min_installment_value) || 5.00;
            
            var installments = [];
            
            for (var i = 1; i <= maxInstallments; i++) {
                var hasInterest = i > installmentsWithoutInterest;
                var total, installmentValue;
                
                if (hasInterest) {
                    // Fórmula de juros compostos: PV * (1 + i)^n
                    total = price * Math.pow(1 + (interestRate / 100), i);
                    installmentValue = total / i;
                } else {
                    // Sem juros
                    total = price;
                    installmentValue = price / i;
                }
                
                // Verifica se a parcela mínima foi atingida
                if (installmentValue < minInstallmentValue) {
                    break;
                }
                
                installments.push({
                    numero: i,
                    valor: Math.round(installmentValue * 100) / 100,
                    total: Math.round(total * 100) / 100,
                    hasInterest: hasInterest
                });
            }
            
            return installments;
        },

        // Gera HTML das parcelas
        generateInstallmentsHTML: function(price, installments) {
            if (installments.length === 0) {
                return '';
            }
            
            var strings = this.config.strings;
            var settings = this.config.settings;
            var designSettings = this.config.designSettings || {};
            var tableDisplayType = settings.table_display_type || 'accordion';
            
            // Melhor condição
            var best = installments[installments.length - 1];
            var priceFormatted = this.formatPrice(price);
            var installmentValueFormatted = this.formatPrice(best.valor);
            
            // Textos por localização (sempre 'product' para variações)
            var location = 'product';
            var locationTexts = settings.location_texts && settings.location_texts[location] ? settings.location_texts[location] : {};
            
            // Texto antes das parcelas
            var textBefore = '';
            if (locationTexts.text_before) {
                textBefore = locationTexts.text_before + ' ';
            } else if (settings.text_before_installments) {
                textBefore = settings.text_before_installments + ' ';
            }
            
            // Texto após as parcelas
            var textAfter = '';
            if (locationTexts.text_after) {
                textAfter = ' ' + locationTexts.text_after;
            } else if (settings.text_after_installments) {
                textAfter = ' ' + settings.text_after_installments;
            } else if (!best.hasInterest) {
                textAfter = ' ' + (strings.withoutInterest || 'sem juros');
            }
            
            // Ícone do cartão (se habilitado)
            var iconHtml = '';
            var iconPosition = designSettings.credit_card_icon_position || 'before';
            var showIcon = designSettings.show_credit_card_icon === '1' || designSettings.show_credit_card_icon === 1;
            
            if (showIcon && iconPosition !== 'none') {
                iconHtml = '<span class="dw-parcelas-icon">' + this.getCreditCardIconHtml(designSettings) + '</span>';
            }
            
            // Gera estilos para o resumo
            var summaryStyles = this.generateSummaryStyles(designSettings);
            
            var html = '<div class="dw-parcelas-container dw-parcelas-summary-container">';
            
            // Resumo
            html += '<div class="dw-parcelas-summary" style="' + summaryStyles.container + '">';
            
            // Posiciona ícone antes ou depois do texto
            if (iconPosition === 'before' && iconHtml) {
                html += iconHtml;
            }
            
            html += '<span class="dw-parcelas-text" style="' + summaryStyles.text + '">';
            html += textBefore;
            html += 'até ' + best.numero + 'x de ' + installmentValueFormatted + textAfter;
            html += '</span>';
            
            if (iconPosition === 'after' && iconHtml) {
                html += iconHtml;
            }
            
            html += '</div>';
            
            // Tabela (só exibe se show_table estiver ativo)
            if (settings.show_table === '1' || settings.show_table === 1) {
                var wrapperId = 'dw-parcelas-wrapper-' + Date.now();
                var wrapperClass = 'dw-parcelas-table-wrapper dw-parcelas-display-' + tableDisplayType;
                html += '<div id="' + wrapperId + '" class="' + wrapperClass + '" data-display-type="' + tableDisplayType + '">';
                
                var tableClass = 'dw-parcelas-table';
                var tableId = 'dw-parcelas-table-' + Date.now();
                
                if (tableDisplayType === 'open') {
                    tableClass += ' dw-parcelas-table-visible';
                } else {
                    tableClass += ' dw-parcelas-table-hidden';
                    var buttonClass = 'dw-parcelas-toggle-btn dw-parcelas-btn-' + tableDisplayType;
                    html += '<button type="button" class="' + buttonClass + '" data-target="' + tableId + '" data-wrapper="' + wrapperId + '">';
                    html += strings.showText || 'Ver todas as parcelas';
                    html += '</button>';
                }
                
                // Para popup, cria estrutura diferente
                if (tableDisplayType === 'popup') {
                    // Remove classe hidden para popup
                    tableClass = tableClass.replace('dw-parcelas-table-hidden', '').trim();
                    html += '<div class="dw-parcelas-popup-content-hidden" style="display:none;">';
                    html += '<table id="' + tableId + '" class="' + tableClass + '" style="display:table; width:100%;">';
                } else {
                    // Para accordion ou aberto, usa container normal
                    html += '<div class="dw-parcelas-table-container" style="' + (tableDisplayType === 'open' ? '' : 'display:none;') + '">';
                    html += '<table id="' + tableId + '" class="' + tableClass + '">';
                }
            html += '<thead><tr>';
            html += '<th>' + (strings.installmentsLabel || 'Parcelas') + '</th>';
            html += '<th>' + (strings.totalLabel || 'Total') + '</th>';
            html += '</tr></thead>';
            html += '<tbody>';
            
            for (var i = 0; i < installments.length; i++) {
                var inst = installments[i];
                var rowClass = inst.hasInterest ? '' : 'dw-parcelas-no-interest';
                
                html += '<tr class="' + rowClass + '">';
                html += '<td>';
                html += inst.numero + 'x de ' + this.formatPrice(inst.valor);
                if (!inst.hasInterest) {
                    html += ' <span class="dw-parcelas-label">' + (strings.withoutInterest || 'sem juros') + '</span>';
                }
                html += '</td>';
                html += '<td><strong>' + this.formatPrice(inst.total) + '</strong></td>';
                html += '</tr>';
            }
            
                html += '</tbody></table>';
            
                if (tableDisplayType === 'popup') {
                    html += '<button type="button" class="dw-parcelas-popup-close" data-target="' + wrapperId + '">' + (strings.closeText || 'Fechar') + '</button>';
                    html += '</div>'; // .dw-parcelas-popup-content-hidden
                } else {
                    html += '</div>'; // .dw-parcelas-table-container
                }
                
                html += '</div>'; // .table-wrapper
            }
            html += '</div>'; // .container
            
            return html;
        },

        // Gera estilos para o resumo
        generateSummaryStyles: function(designSettings) {
            var bgColor = designSettings.background_color || '#f5f5f5';
            var allowTransparent = designSettings.allow_transparent_background === '1' || designSettings.allow_transparent_background === 1;
            
            // Se permitir transparente e a cor for vazia ou "transparent", usa transparente
            if (allowTransparent && (bgColor === '' || bgColor === 'transparent' || bgColor.toLowerCase() === 'transparent')) {
                bgColor = 'transparent';
            }
            
            var borderColor = designSettings.border_color || '#2c3e50';
            var borderStyle = designSettings.border_style || 'solid';
            var textColor = designSettings.text_color || '#333333';
            var fontSize = designSettings.font_size || '16';
            
            // Se border_style for 'none', não adiciona borda
            var borderCss = '';
            if (borderStyle !== 'none') {
                borderCss = 'border-left: 4px ' + borderStyle + ' ' + borderColor + ';';
            }
            
            return {
                container: 'background-color: ' + bgColor + '; ' + borderCss + ' padding: 15px; border-radius: 8px; margin-bottom: 10px;',
                text: 'color: ' + textColor + '; font-size: ' + fontSize + 'px;'
            };
        },

        // Formata preço no formato brasileiro
        formatPrice: function(value) {
            value = parseFloat(value);
            if (isNaN(value)) {
                return 'R$ 0,00';
            }
            
            // Formata com 2 casas decimais
            var formatted = value.toFixed(2);
            
            // Substitui ponto por vírgula nos decimais
            formatted = formatted.replace('.', ',');
            
            // Adiciona pontos como separadores de milhares
            var parts = formatted.split(',');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            
            return 'R$ ' + parts.join(',');
        },

        // Retorna HTML do ícone do cartão
        getCreditCardIconHtml: function(designSettings) {
            var iconUrl = designSettings.credit_card_icon_custom || '';
            var defaultIconUrl = '';
            
            // Ícone SVG padrão inline
            var defaultSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4C2.89 4 2.01 4.89 2.01 6L2 18C2 19.11 2.89 20 4 20H20C21.11 20 22 19.11 22 18V6C22 4.89 21.11 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8Z" fill="currentColor"/></svg>';
            
            if (!iconUrl) {
                return defaultSvg;
            }
            
            // Verifica se é SVG ou imagem
            var extension = iconUrl.toLowerCase().split('.').pop();
            
            if (extension === 'svg') {
                // Para SVG, retorna tag img que carrega o SVG
                return '<img src="' + iconUrl + '" alt="Cartão" style="width: 20px; height: 20px; vertical-align: middle; display: inline-block;" />';
            } else {
                // Para imagens (PNG, JPG, etc)
                return '<img src="' + iconUrl + '" alt="Cartão" class="dw-parcelas-credit-card-icon-image" style="width: 20px; height: 20px; vertical-align: middle; display: inline-block;" />';
            }
        },

        // Oculta as parcelas
        hideInstallments: function() {
            this.config.container.hide();
        }
    };

    // Expõe a classe globalmente
    window.DWVariableInstallments = VariableInstallments;

    // Funções para accordion e popup de parcelas
    var ParcelasAccordion = {
        
        // Inicializa controles (accordion/popup)
        init: function() {
            // Remove eventos anteriores para evitar duplicação
            $(document).off('click', '.dw-parcelas-toggle-btn');
            $(document).off('click', '.dw-parcelas-popup-close');
            $(document).off('click', '.dw-parcelas-popup-overlay');
            
            // Accordion
            $(document).on('click', '.dw-parcelas-toggle-btn.dw-parcelas-btn-accordion', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var targetId = $btn.data('target');
                var $table = $('#' + targetId);
                var $container = $table.closest('.dw-parcelas-table-container');
                
                var strings = (typeof window.dwParcelasData !== 'undefined' && window.dwParcelasData.strings) ? window.dwParcelasData.strings : {};
                var showText = strings.showText || 'Ver todas as parcelas';
                var hideText = strings.hideText || 'Ocultar parcelas';
                
                // Verifica se o container está visível
                var isVisible = $container.length ? $container.is(':visible') : $table.is(':visible');
                
                if (!isVisible || $table.hasClass('dw-parcelas-table-hidden')) {
                    // Abre
                    $table.removeClass('dw-parcelas-table-hidden').addClass('dw-parcelas-table-visible');
                    $table.css('display', 'table');
                    
                    if ($container.length) {
                        $container.slideDown(300);
                    } else {
                        $table.slideDown(300);
                    }
                    $btn.text(hideText).addClass('dw-parcelas-active');
                } else {
                    // Fecha
                    $table.removeClass('dw-parcelas-table-visible').addClass('dw-parcelas-table-hidden');
                    
                    if ($container.length) {
                        $container.slideUp(300);
                    } else {
                        $table.slideUp(300);
                    }
                    $btn.text(showText).removeClass('dw-parcelas-active');
                }
            });
            
            // Popup - Abrir
            $(document).on('click', '.dw-parcelas-toggle-btn.dw-parcelas-btn-popup', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var wrapperId = $btn.data('wrapper');
                var $wrapper = $('#' + wrapperId);
                var $popupHidden = $wrapper.find('.dw-parcelas-popup-content-hidden');
                
                if ($popupHidden.length === 0) {
                    return;
                }
                
                // Copia o conteúdo para criar o popup (clona para manter eventos)
                var $popupClone = $popupHidden.clone(true);
                $popupClone.removeClass('dw-parcelas-popup-content-hidden');
                
                // Remove popup anterior se existir
                $('.dw-parcelas-popup-content-active').remove();
                
                // Adiciona classes e estilos ao clone
                $popupClone.addClass('dw-parcelas-popup-content dw-parcelas-popup-content-active');
                $popupClone.css({
                    'display': 'block',
                    'position': 'fixed',
                    'top': '50%',
                    'left': '50%',
                    'transform': 'translate(-50%, -50%)',
                    'z-index': '9999',
                    'background': '#fff',
                    'border-radius': '8px',
                    'padding': '30px',
                    'max-width': '90%',
                    'width': '600px',
                    'max-height': '80vh',
                    'overflow-y': 'auto',
                    'box-shadow': '0 4px 20px rgba(0, 0, 0, 0.3)'
                });
                
                // Garante que a tabela está visível
                var $table = $popupClone.find('table');
                $table.removeClass('dw-parcelas-table-hidden').css({
                    'display': 'table',
                    'width': '100%'
                });
                
                // Garante que tbody e tr estão visíveis
                $popupClone.find('tbody, tbody tr, tbody td').css('display', '');
                
                // Adiciona ao body
                $('body').append($popupClone);
                
                // Cria overlay se não existir
                if ($('.dw-parcelas-popup-overlay').length === 0) {
                    $('body').append('<div class="dw-parcelas-popup-overlay"></div>');
                }
                
                // Exibe overlay
                $('.dw-parcelas-popup-overlay').fadeIn(300);
                $wrapper.addClass('dw-parcelas-popup-active');
                
                // Previne scroll do body
                $('body').addClass('dw-parcelas-popup-open');
            });
            
            // Popup - Fechar via botão
            $(document).on('click', '.dw-parcelas-popup-close', function(e) {
                e.preventDefault();
                ParcelasAccordion.closePopup($(this).data('target'));
            });
            
            // Popup - Fechar via overlay
            $(document).on('click', '.dw-parcelas-popup-overlay', function(e) {
                if ($(e.target).hasClass('dw-parcelas-popup-overlay')) {
                    $('.dw-parcelas-popup-active').each(function() {
                        ParcelasAccordion.closePopup($(this).attr('id'));
                    });
                }
            });
            
            // Popup - Fechar via ESC
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // ESC
                    $('.dw-parcelas-popup-active').each(function() {
                        ParcelasAccordion.closePopup($(this).attr('id'));
                    });
                }
            });
        },
        
        // Fecha popup
        closePopup: function(wrapperId) {
            var $popupContent = $('.dw-parcelas-popup-content-active');
            
            $popupContent.fadeOut(300, function() {
                $(this).remove();
            });
            
            $('.dw-parcelas-popup-overlay').fadeOut(300, function() {
                $(this).remove();
            });
            
            if (wrapperId) {
                var $wrapper = $('#' + wrapperId);
                $wrapper.removeClass('dw-parcelas-popup-active');
            }
            
            $('body').removeClass('dw-parcelas-popup-open');
        }
    };

    // Inicializa quando o documento está pronto
    $(document).ready(function() {
        ParcelasAccordion.init();
        
        // REMOVIDO: MutationObserver causava loop infinito
        // Os eventos são registrados com delegation (document.on) então funcionam automaticamente
    });

})(jQuery);

