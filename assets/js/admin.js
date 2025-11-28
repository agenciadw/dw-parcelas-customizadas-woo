/**
 * JavaScript para admin - DW Parcelas e Pix Customizadas
 * 
 * Este arquivo contém toda a lógica JavaScript para o painel administrativo do plugin,
 * incluindo preview em tempo real, gerenciamento de ícones e funcionalidades de exportação/importação.
 *
 * @package DW_Parcelas_Pix_WooCommerce
 * @since 0.2.0
 */

(function($) {
    'use strict';

    /**
     * Classe AdminPreview
     * 
     * Gerencia o preview em tempo real das configurações no painel administrativo.
     * Atualiza o preview automaticamente quando o usuário altera cores, textos ou outros campos.
     */
    var AdminPreview = {
        
        /**
         * Inicializa o sistema de preview
         * 
         * Vincula eventos aos campos do formulário e atualiza o preview inicial.
         */
        init: function() {
            this.bindEvents();
            this.updatePreview();
        },

        /**
         * Vincula eventos aos campos do formulário
         * 
         * Monitora mudanças em campos de cor, texto, select e checkboxes
         * para atualizar o preview em tempo real.
         */
        bindEvents: function() {
            var self = this;
            
            // Eventos para campos de cor
            $('input[type="color"]').on('change', function() {
                self.updatePreview();
            });
            
            // Eventos para campos de texto
            $('input[type="text"]').on('input', function() {
                self.updatePreview();
            });
            
            // Eventos para selects
            $('select').on('change', function() {
                self.updatePreview();
            });
            
            // Eventos para checkboxes (parcelas)
            $('input[type="checkbox"][name*="installments_design_settings"]').on('change', function() {
                self.updatePreview();
            });
        },

        /**
         * Atualiza o preview com as configurações atuais
         * 
         * Obtém as configurações dos campos e aplica os estilos ao preview.
         */
        updatePreview: function() {
            var settings = this.getCurrentSettings();
            this.applyStyles(settings);
        },

        /**
         * Obtém as configurações atuais dos campos do formulário
         * 
         * @return {Object} Objeto contendo todas as configurações atuais
         */
        getCurrentSettings: function() {
            var iconUrl = $('#dw-pix-icon-url').val();
            var defaultIconUrl = $('#dw-pix-icon-preview').attr('data-default') || '';
            
            return {
                background_color: $('input[name="dw_pix_design_settings[background_color]"]').val() || '#e8f5e9',
                border_color: $('input[name="dw_pix_design_settings[border_color]"]').val() || '#4caf50',
                text_color: $('input[name="dw_pix_design_settings[text_color]"]').val() || '#2e7d32',
                price_color: $('input[name="dw_pix_design_settings[price_color]"]').val() || '#1b5e20',
                pix_icon_custom: iconUrl || defaultIconUrl,
                custom_text: $('input[name="dw_pix_design_settings[custom_text]"]').val() || 'Pagando com PIX:',
                discount_text: $('input[name="dw_pix_design_settings[discount_text]"]').val() || 'de desconto',
                discount_text_color: $('input[name="dw_pix_design_settings[discount_text_color]"]').val() || '#666',
                border_style: $('select[name="dw_pix_design_settings[border_style]"]').val() || 'solid',
                font_size: $('select[name="dw_pix_design_settings[font_size]"]').val() || '16'
            };
        },

        /**
         * Aplica os estilos ao elemento de preview
         * 
         * @param {Object} settings - Configurações a serem aplicadas
         */
        applyStyles: function(settings) {
            var $preview = $('#dw-pix-preview');
            var $text = $preview.find('.dw-pix-price-text');
            var $icon = $preview.find('.pix-icon');
            var $customText = $preview.find('.custom-text');
            var $price = $preview.find('.dw-pix-price-amount');
            var $discount = $preview.find('.dw-pix-discount-percent');

            // Aplica estilos ao container
            $preview.css({
                'background-color': settings.background_color,
                'border-left-color': settings.border_color,
                'border-left-style': settings.border_style
            });

            // Aplica estilos ao texto
            $text.css({
                'color': settings.text_color,
                'font-size': settings.font_size + 'px'
            });

            // Aplica estilos ao preço
            $price.css({
                'color': settings.price_color
            });

            // Aplica estilos ao desconto
            $discount.css({
                'color': settings.discount_text_color
            });

            // Atualiza ícone (sempre imagem)
            if (settings.pix_icon_custom) {
                $icon.html('<img src="' + settings.pix_icon_custom + '" alt="PIX" style="width: 20px; height: 20px; vertical-align: middle;" />');
                $icon.show();
            } else {
                // Usa ícone padrão se não houver personalizado
                var defaultIconUrl = $('#dw-pix-icon-preview').attr('data-default') || '';
                if (defaultIconUrl) {
                    $icon.html('<img src="' + defaultIconUrl + '" alt="PIX" style="width: 20px; height: 20px; vertical-align: middle;" />');
                    $icon.show();
                } else {
                    $icon.hide();
                }
            }
            
            $customText.text(settings.custom_text);
            
            // Atualiza texto de desconto
            var $discount = $preview.find('.dw-pix-discount-percent');
            $discount.text('(10% ' + settings.discount_text + ')');

            // Adiciona classe para animação
            $preview.addClass('dw-pix-live-preview');
            
            // Remove a classe após a animação
            setTimeout(function() {
                $preview.removeClass('dw-pix-live-preview');
            }, 300);
            
            // Atualiza preview das parcelas
            this.updateParcelasPreview();
        },
        
        /**
         * Atualiza o preview das configurações de parcelas
         * 
         * Aplica as configurações de design das parcelas ao preview correspondente.
         */
        updateParcelasPreview: function() {
            var installmentsSettings = this.getInstallmentsDesignSettings();
            var $preview = $('#dw-parcelas-preview');
            
            if ($preview.length === 0) {
                return;
            }
            
            var $text = $preview.find('.dw-parcelas-text');
            var $icon = $preview.find('.dw-parcelas-icon');
            
            // Cor de fundo (permite transparente)
            var bgColor = installmentsSettings.background_color || '#f5f5f5';
            var allowTransparent = installmentsSettings.allow_transparent_background === '1' || installmentsSettings.allow_transparent_background === 1;
            
            if (allowTransparent && (bgColor === '' || bgColor === 'transparent' || bgColor.toLowerCase() === 'transparent')) {
                bgColor = 'transparent';
            }
            
            // Borda
            var borderStyle = installmentsSettings.border_style || 'solid';
            var borderColor = installmentsSettings.border_color || '#2c3e50';
            var borderCss = '';
            if (borderStyle !== 'none') {
                borderCss = '4px ' + borderStyle + ' ' + borderColor;
            }
            
            // Aplica estilos ao container
            $preview.css({
                'background-color': bgColor,
                'border-left': borderCss || 'none',
                'padding': '15px',
                'border-radius': '8px',
                'margin-bottom': '10px'
            });
            
            // Aplica estilos ao texto
            $text.css({
                'color': installmentsSettings.text_color || '#333333',
                'font-size': (installmentsSettings.font_size || '16') + 'px'
            });
            
            // Ícone
            var iconPosition = installmentsSettings.credit_card_icon_position || 'before';
            var showIcon = installmentsSettings.show_credit_card_icon === '1' || installmentsSettings.show_credit_card_icon === 1;
            
            // Remove ícone atual e reposiciona
            var iconHtml = '';
            if (showIcon && iconPosition !== 'none') {
                var iconUrl = installmentsSettings.credit_card_icon_custom || '';
                var defaultIconUrl = $('#dw-parcelas-credit-card-icon-preview').attr('data-default') || '';
                if (!iconUrl) {
                    iconUrl = defaultIconUrl;
                }
                
                if (iconUrl) {
                    iconHtml = '<span class="dw-parcelas-icon"><img src="' + iconUrl + '" alt="Cartão" style="width: 20px; height: 20px; vertical-align: middle;" /></span>';
                }
            }
            
            // Remove ícone existente
            $icon.remove();
            
            // Adiciona ícone na posição correta
            if (iconHtml) {
                if (iconPosition === 'before') {
                    $preview.prepend(iconHtml);
                } else if (iconPosition === 'after') {
                    $preview.append(iconHtml);
                }
            }
        },
        
        /**
         * Obtém as configurações de design das parcelas
         * 
         * @return {Object} Objeto contendo as configurações de design das parcelas
         */
        getInstallmentsDesignSettings: function() {
            var iconUrl = $('#dw-parcelas-credit-card-icon-url').val();
            var defaultIconUrl = $('#dw-parcelas-credit-card-icon-preview').attr('data-default') || '';
            
            return {
                background_color: $('input[name="dw_pix_installments_design_settings[background_color]"]').val() || '#f5f5f5',
                border_color: $('input[name="dw_pix_installments_design_settings[border_color]"]').val() || '#2c3e50',
                text_color: $('input[name="dw_pix_installments_design_settings[text_color]"]').val() || '#333333',
                price_color: $('input[name="dw_pix_installments_design_settings[price_color]"]').val() || '#2c3e50',
                border_style: $('select[name="dw_pix_installments_design_settings[border_style]"]').val() || 'solid',
                font_size: $('select[name="dw_pix_installments_design_settings[font_size]"]').val() || '16',
                credit_card_icon_custom: iconUrl || defaultIconUrl,
                show_credit_card_icon: $('input[name="dw_pix_installments_design_settings[show_credit_card_icon]"]').is(':checked') ? '1' : '0',
                credit_card_icon_position: $('select[name="dw_pix_installments_design_settings[credit_card_icon_position]"]').val() || 'before',
                allow_transparent_background: $('input[name="dw_pix_installments_design_settings[allow_transparent_background]"]').is(':checked') ? '1' : '0'
            };
        }
    };

    /**
     * Classe AdminFeatures
     * 
     * Gerencia funcionalidades adicionais do painel administrativo como:
     * - Upload de ícones
     * - Exportação/Importação de configurações
     * - Reset de configurações
     * - Tooltips de ajuda
     */
    var AdminFeatures = {
        
        /**
         * Inicializa as funcionalidades adicionais
         */
        init: function() {
            this.addResetButton();
            // Removido addExportImport() - os botões de Exportar/Importar estão apenas na aba Avançado
            this.addHelpTooltips();
            this.initIconUpload();
        },

        /**
         * Adiciona botão de reset ao formulário
         * 
         * O botão permite resetar as configurações para os valores padrão.
         */
        addResetButton: function() {
            var $submitButton = $('.submit .button-primary');
            var resetButton = '<button type="button" class="button dw-pix-reset-button" onclick="AdminFeatures.resetToDefaults()">' +
                             'Resetar para Padrão' +
                             '</button>';
            $submitButton.after(resetButton);
        },

        /**
         * Reseta as configurações para os valores padrão
         * 
         * Solicita confirmação do usuário antes de realizar o reset.
         */
        resetToDefaults: function() {
            if (confirm('Tem certeza que deseja resetar todas as configurações para os valores padrão?')) {
                // Reset dos campos
                $('input[name="dw_pix_design_settings[background_color]"]').val('#e8f5e9');
                $('input[name="dw_pix_design_settings[border_color]"]').val('#4caf50');
                $('input[name="dw_pix_design_settings[text_color]"]').val('#2e7d32');
                $('input[name="dw_pix_design_settings[price_color]"]').val('#1b5e20');
                $('#dw-pix-icon-url').val('');
                var defaultIconUrl = $('#dw-pix-icon-preview').attr('data-default');
                if (defaultIconUrl) {
                    $('#dw-pix-icon-preview').attr('src', defaultIconUrl);
                }
                $('#dw-pix-remove-icon').hide();
                $('input[name="dw_pix_design_settings[custom_text]"]').val('Pagando com PIX:');
                $('input[name="dw_pix_design_settings[discount_text]"]').val('de desconto');
                $('input[name="dw_pix_design_settings[discount_text_color]"]').val('#666');
                $('select[name="dw_pix_design_settings[border_style]"]').val('solid');
                $('select[name="dw_pix_design_settings[font_size]"]').val('16');
                
                // Atualiza preview
                AdminPreview.updatePreview();
                
                // Mostra mensagem de sucesso
                this.showMessage('Configurações resetadas para os valores padrão!', 'success');
            }
        },

        // Removido: addExportImport() - os botões de Exportar/Importar estão apenas na aba Avançado (renderizados via PHP)

        /**
         * Exporta as configurações atuais para um arquivo JSON
         * 
         * Gera um arquivo JSON com todas as configurações e inicia o download.
         */
        exportSettings: function() {
            var settings = AdminPreview.getCurrentSettings();
            var dataStr = JSON.stringify(settings, null, 2);
            var dataBlob = new Blob([dataStr], {type: 'application/json'});
            
            var link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = 'dw-pix-settings.json';
            link.click();
            
            this.showMessage('Configurações exportadas com sucesso!', 'success');
        },

        /**
         * Importa configurações de um arquivo JSON
         * 
         * Permite ao usuário selecionar um arquivo JSON com configurações
         * e aplica essas configurações ao formulário.
         */
        importSettings: function() {
            var input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            
            input.onchange = function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            var settings = JSON.parse(e.target.result);
                            AdminFeatures.applyImportedSettings(settings);
                        } catch (error) {
                            AdminFeatures.showMessage('Erro ao importar arquivo. Verifique se é um arquivo válido.', 'error');
                        }
                    };
                    reader.readAsText(file);
                }
            };
            
            input.click();
        },

        /**
         * Aplica as configurações importadas aos campos do formulário
         * 
         * @param {Object} settings - Configurações importadas do arquivo JSON
         */
        applyImportedSettings: function(settings) {
            if (settings.background_color) $('input[name="dw_pix_design_settings[background_color]"]').val(settings.background_color);
            if (settings.border_color) $('input[name="dw_pix_design_settings[border_color]"]').val(settings.border_color);
            if (settings.text_color) $('input[name="dw_pix_design_settings[text_color]"]').val(settings.text_color);
            if (settings.price_color) $('input[name="dw_pix_design_settings[price_color]"]').val(settings.price_color);
            if (settings.pix_icon_custom) {
                $('#dw-pix-icon-url').val(settings.pix_icon_custom);
                $('#dw-pix-icon-preview').attr('src', settings.pix_icon_custom);
                $('#dw-pix-remove-icon').show();
            } else {
                // Se não houver ícone personalizado, usa o padrão
                var defaultIconUrl = $('#dw-pix-icon-preview').attr('data-default');
                if (defaultIconUrl) {
                    $('#dw-pix-icon-url').val('');
                    $('#dw-pix-icon-preview').attr('src', defaultIconUrl);
                    $('#dw-pix-remove-icon').hide();
                }
            }
            if (settings.custom_text) $('input[name="dw_pix_design_settings[custom_text]"]').val(settings.custom_text);
            if (settings.discount_text) $('input[name="dw_pix_design_settings[discount_text]"]').val(settings.discount_text);
            if (settings.discount_text_color) $('input[name="dw_pix_design_settings[discount_text_color]"]').val(settings.discount_text_color);
            if (settings.border_style) $('select[name="dw_pix_design_settings[border_style]"]').val(settings.border_style);
            if (settings.font_size) $('select[name="dw_pix_design_settings[font_size]"]').val(settings.font_size);
            
            AdminPreview.updatePreview();
            this.showMessage('Configurações importadas com sucesso!', 'success');
        },

        /**
         * Adiciona tooltips de ajuda aos campos do formulário
         * 
         * Exibe ícones de ajuda ao lado dos rótulos dos campos.
         */
        addHelpTooltips: function() {
            $('.form-table th').each(function() {
                var $th = $(this);
                var text = $th.text();
                
                // Adiciona ícone de ajuda
                var helpIcon = '<span class="dashicons dashicons-editor-help" style="margin-left: 5px; cursor: help;" title="' + text + '"></span>';
                $th.append(helpIcon);
            });
        },

        /**
         * Inicializa o sistema de upload de ícones
         * 
         * Configura os eventos para upload, remoção e reset de ícones personalizados.
         * Suporta ícones PIX e de cartão de crédito para página do produto e galeria.
         */
        initIconUpload: function() {
            var self = this;
            var $uploadButton = $('#dw-pix-upload-icon');
            var $removeButton = $('#dw-pix-remove-icon');
            var $resetButton = $('#dw-pix-reset-icon');
            var $iconUrl = $('#dw-pix-icon-url');
            var $preview = $('#dw-pix-icon-preview');
            var defaultIconUrl = $preview.attr('src');
            
            // Upload de ícone do cartão de crédito
            this.initCreditCardIconUpload();
            
            // Upload de ícone PIX para galeria
            this.initPixIconGalleryUpload();
            
            // Upload de ícone do cartão para galeria
            this.initCreditCardIconGalleryUpload();

            // Upload de ícone
            if ($uploadButton.length) {
                $uploadButton.on('click', function(e) {
                    e.preventDefault();
                    
                    var mediaUploader = wp.media({
                        title: 'Escolher Ícone PIX',
                        button: {
                            text: 'Usar este ícone'
                        },
                        multiple: false,
                        library: {
                            type: ['image/svg+xml', 'image/png', 'image/jpeg']
                        }
                    });

                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $iconUrl.val(attachment.url);
                        $preview.attr('src', attachment.url);
                        $removeButton.show();
                        AdminPreview.updatePreview();
                    });

                    mediaUploader.open();
                });
            }

            // Remover ícone personalizado
            if ($removeButton.length) {
                $removeButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $(this).hide();
                    AdminPreview.updatePreview();
                });
            }

            // Resetar para ícone padrão
            if ($resetButton.length) {
                $resetButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $removeButton.hide();
                    AdminPreview.updatePreview();
                });
            }
        },

        /**
         * Inicializa o upload de ícone do cartão de crédito
         * 
         * Configura eventos para upload, remoção e reset do ícone do cartão.
         */
        initCreditCardIconUpload: function() {
            var $uploadButton = $('#dw-parcelas-upload-credit-card-icon');
            var $removeButton = $('#dw-parcelas-remove-credit-card-icon');
            var $resetButton = $('#dw-parcelas-reset-credit-card-icon');
            var $iconUrl = $('#dw-parcelas-credit-card-icon-url');
            var $preview = $('#dw-parcelas-credit-card-icon-preview');
            var defaultIconUrl = $preview.attr('data-default') || $preview.attr('src');

            // Upload de ícone
            if ($uploadButton.length) {
                $uploadButton.on('click', function(e) {
                    e.preventDefault();
                    
                    var mediaUploader = wp.media({
                        title: 'Escolher Ícone do Cartão',
                        button: {
                            text: 'Usar este ícone'
                        },
                        multiple: false,
                        library: {
                            type: ['image/svg+xml', 'image/png', 'image/jpeg']
                        }
                    });

                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $iconUrl.val(attachment.url);
                        $preview.attr('src', attachment.url);
                        $removeButton.show();
                    });

                    mediaUploader.open();
                });
            }

            // Remover ícone personalizado
            if ($removeButton.length) {
                $removeButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $(this).hide();
                });
            }

            // Resetar para ícone padrão
            if ($resetButton.length) {
                $resetButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $removeButton.hide();
                });
            }
        },

        // Inicializa upload de ícone PIX para galeria
        initPixIconGalleryUpload: function() {
            var $uploadButton = $('#dw-pix-upload-icon-gallery');
            var $removeButton = $('#dw-pix-remove-icon-gallery');
            var $resetButton = $('#dw-pix-reset-icon-gallery');
            var $iconUrl = $('#dw-pix-icon-gallery-url');
            var $preview = $('#dw-pix-icon-gallery-preview');
            var defaultIconUrl = $preview.attr('data-default') || $preview.attr('src');

            // Upload de ícone
            if ($uploadButton.length) {
                $uploadButton.on('click', function(e) {
                    e.preventDefault();
                    
                    var mediaUploader = wp.media({
                        title: 'Escolher Ícone PIX para Galeria',
                        button: {
                            text: 'Usar este ícone'
                        },
                        multiple: false,
                        library: {
                            type: ['image/svg+xml', 'image/png', 'image/jpeg']
                        }
                    });

                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $iconUrl.val(attachment.url);
                        $preview.attr('src', attachment.url);
                        $removeButton.show();
                    });

                    mediaUploader.open();
                });
            }

            // Remover ícone personalizado
            if ($removeButton.length) {
                $removeButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $(this).hide();
                });
            }

            // Resetar para ícone padrão
            if ($resetButton.length) {
                $resetButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $removeButton.hide();
                });
            }
        },

        // Inicializa upload de ícone do cartão para galeria
        initCreditCardIconGalleryUpload: function() {
            var $uploadButton = $('#dw-parcelas-upload-credit-card-icon-gallery');
            var $removeButton = $('#dw-parcelas-remove-credit-card-icon-gallery');
            var $resetButton = $('#dw-parcelas-reset-credit-card-icon-gallery');
            var $iconUrl = $('#dw-parcelas-credit-card-icon-gallery-url');
            var $preview = $('#dw-parcelas-credit-card-icon-gallery-preview');
            var defaultIconUrl = $preview.attr('data-default') || $preview.attr('src');

            // Upload de ícone
            if ($uploadButton.length) {
                $uploadButton.on('click', function(e) {
                    e.preventDefault();
                    
                    var mediaUploader = wp.media({
                        title: 'Escolher Ícone do Cartão para Galeria',
                        button: {
                            text: 'Usar este ícone'
                        },
                        multiple: false,
                        library: {
                            type: ['image/svg+xml', 'image/png', 'image/jpeg']
                        }
                    });

                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $iconUrl.val(attachment.url);
                        $preview.attr('src', attachment.url);
                        $removeButton.show();
                    });

                    mediaUploader.open();
                });
            }

            // Remover ícone personalizado
            if ($removeButton.length) {
                $removeButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $(this).hide();
                });
            }

            // Resetar para ícone padrão
            if ($resetButton.length) {
                $resetButton.on('click', function(e) {
                    e.preventDefault();
                    $iconUrl.val('');
                    $preview.attr('src', defaultIconUrl);
                    $removeButton.hide();
                });
            }
        },

        /**
         * Exibe uma mensagem de notificação no topo da página
         * 
         * @param {string} message - Mensagem a ser exibida
         * @param {string} type - Tipo da mensagem (success, error, warning, info)
         */
        showMessage: function(message, type) {
            var $message = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($message);
            
            setTimeout(function() {
                $message.fadeOut();
            }, 3000);
        }
    };

    /**
     * Classe TablePreview
     * 
     * Gerencia o preview em tempo real das configurações de design da tabela de parcelas.
     * Atualiza o preview automaticamente quando o usuário altera as cores da tabela.
     */
    var TablePreview = {
        
        /**
         * Inicializa o sistema de preview da tabela
         */
        init: function() {
            // Só inicializa se o preview da tabela existir na página
            if ($('#dw-table-preview').length === 0) {
                return;
            }
            
            this.bindEvents();
            this.updatePreview();
        },
        
        /**
         * Vincula eventos aos campos de cor da tabela
         */
        bindEvents: function() {
            var self = this;
            
            // Monitora todos os campos de cor da aba de design da tabela
            $('input[name*="dw_pix_table_design_settings"]').on('change input', function() {
                self.updatePreview();
            });
        },
        
        /**
         * Atualiza o preview da tabela com as cores selecionadas
         */
        updatePreview: function() {
            var settings = this.getCurrentSettings();
            this.applyStyles(settings);
        },
        
        /**
         * Coleta as configurações atuais dos campos de cor
         * 
         * @returns {object} Objeto com todas as configurações de cor da tabela
         */
        getCurrentSettings: function() {
            return {
                table_background_color: $('input[name="dw_pix_table_design_settings[table_background_color]"]').val() || '#fafafa',
                table_header_background_color: $('input[name="dw_pix_table_design_settings[table_header_background_color]"]').val() || '#f0f4f8',
                table_header_text_color: $('input[name="dw_pix_table_design_settings[table_header_text_color]"]').val() || '#4a658a',
                table_cell_text_color: $('input[name="dw_pix_table_design_settings[table_cell_text_color]"]').val() || '#333333',
                table_row_even_color: $('input[name="dw_pix_table_design_settings[table_row_even_color]"]').val() || '#f8f9fa',
                table_row_hover_color: $('input[name="dw_pix_table_design_settings[table_row_hover_color]"]').val() || '#eef2f7',
                table_pix_row_color: $('input[name="dw_pix_table_design_settings[table_pix_row_color]"]').val() || '#fff3e0',
                table_pix_text_color: $('input[name="dw_pix_table_design_settings[table_pix_text_color]"]').val() || '#e65100',
                table_no_interest_row_color: $('input[name="dw_pix_table_design_settings[table_no_interest_row_color]"]').val() || '#e6f4ea',
                table_no_interest_text_color: $('input[name="dw_pix_table_design_settings[table_no_interest_text_color]"]').val() || '#2e7d32',
                table_border_color: $('input[name="dw_pix_table_design_settings[table_border_color]"]').val() || '#e9edf2',
                table_cell_padding: $('input[name="dw_pix_table_design_settings[table_cell_padding]"]').val() || '10px 15px'
            };
        },
        
        /**
         * Aplica os estilos ao preview da tabela
         * 
         * @param {object} settings Configurações de cor da tabela
         */
        applyStyles: function(settings) {
            var $preview = $('#dw-table-preview');
            var $table = $preview.find('table');
            
            // Aplica cor de fundo da tabela
            $table.css('background-color', settings.table_background_color);
            
            // Aplica estilos ao cabeçalho
            $table.find('thead').css('background-color', settings.table_header_background_color);
            $table.find('thead th').css({
                'color': settings.table_header_text_color,
                'border-bottom-color': settings.table_border_color,
                'padding': settings.table_cell_padding
            });
            
            // Aplica estilos às células (padrão)
            $table.find('tbody td').css({
                'color': settings.table_cell_text_color,
                'border-bottom-color': settings.table_border_color,
                'padding': settings.table_cell_padding
            });
            
            // Aplica cor à linha PIX
            $table.find('tbody tr').eq(0).css({
                'background-color': settings.table_pix_row_color,
                'color': settings.table_pix_text_color
            });
            $table.find('tbody tr').eq(0).find('td').css('color', settings.table_pix_text_color);
            
            // Aplica cor às linhas sem juros (1x, 2x, 3x)
            $table.find('tbody tr').eq(1).css({
                'background-color': settings.table_no_interest_row_color,
                'color': settings.table_no_interest_text_color
            });
            $table.find('tbody tr').eq(1).find('td').css('color', settings.table_no_interest_text_color);
            
            // Linha 2x (par - zebrado)
            $table.find('tbody tr').eq(2).css({
                'background-color': settings.table_row_even_color,
                'color': settings.table_cell_text_color
            });
            $table.find('tbody tr').eq(2).find('td').css('color', settings.table_cell_text_color);
            
            // Linha 3x (sem juros)
            $table.find('tbody tr').eq(3).css({
                'background-color': settings.table_no_interest_row_color,
                'color': settings.table_no_interest_text_color
            });
            $table.find('tbody tr').eq(3).find('td').css('color', settings.table_no_interest_text_color);
            
            // Linha 4x (par - zebrado)
            $table.find('tbody tr').eq(4).css({
                'background-color': settings.table_row_even_color,
                'color': settings.table_cell_text_color
            });
            $table.find('tbody tr').eq(4).find('td').css('color', settings.table_cell_text_color);
            
            // Adiciona efeito hover
            $table.find('tbody tr').off('mouseenter mouseleave');
            $table.find('tbody tr').hover(
                function() {
                    $(this).css('background-color', settings.table_row_hover_color);
                },
                function() {
                    var index = $(this).index();
                    if (index === 0) {
                        // Linha PIX
                        $(this).css('background-color', settings.table_pix_row_color);
                    } else if (index === 1 || index === 3) {
                        // Linhas sem juros
                        $(this).css('background-color', settings.table_no_interest_row_color);
                    } else {
                        // Linhas pares
                        $(this).css('background-color', settings.table_row_even_color);
                    }
                }
            );
            
            // Animação de transição suave
            $preview.addClass('dw-pix-live-preview');
            setTimeout(function() {
                $preview.removeClass('dw-pix-live-preview');
            }, 300);
        }
    };

    // Inicializa quando o documento estiver pronto
    $(document).ready(function() {
        AdminPreview.init();
        AdminFeatures.init();
        TablePreview.init();
    });

    // Expõe classes globalmente
    window.AdminPreview = AdminPreview;
    window.AdminFeatures = AdminFeatures;
    window.TablePreview = TablePreview;

})(jQuery);
