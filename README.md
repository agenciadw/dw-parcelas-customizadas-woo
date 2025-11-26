# DW Parcelas e Pix Customizadas WooCommerce

Plugin completo para WooCommerce que permite definir preços especiais para pagamento via PIX e exibir parcelas de cartão de crédito de forma profissional em cada produto individualmente.

## Descrição

O **DW Parcelas e Pix Customizadas WooCommerce** é um plugin completo desenvolvido para WooCommerce que oferece duas funcionalidades principais:

1. **Preços PIX Customizados**: Defina preços diferenciados para pagamento via PIX em cada produto, permitindo que lojistas ofereçam descontos especiais para clientes que optam por pagar via PIX.

2. **Parcelas de Cartão de Crédito**: Exiba de forma profissional as opções de parcelamento no cartão de crédito, com ou sem juros, configurável por produto.

## Funcionalidades

### 🏦 Preços PIX
- ✅ **Preço individual por produto**: Defina valores especiais para PIX em cada produto
- ✅ **Suporte completo a variações**: Configure preços PIX diferentes para cada variação com atualização dinâmica
- ✅ **Detecção automática de PIX**: Identifica gateways PIX e aplica descontos automaticamente
- ✅ **Exibição controlada**: Sempre acima do botão de comprar, alinhado às parcelas
- ✅ **Avisos no carrinho**: Notifica o cliente sobre descontos disponíveis
- ✅ **Atualização automática**: Recalcula valores quando forma de pagamento muda
- ✅ **Desconto global**: Configure um desconto padrão para toda a loja
- ✅ **Shortcode e Elementor**: Use `[dw_pix_price]` ou hooks dedicados para builders

### 💳 Parcelas de Cartão
- ✅ **Parcelamento flexível**: Configure até 12x com ou sem juros
- ✅ **Parcelas sem juros**: Defina quantas parcelas não terão juros
- ✅ **Taxa de juros customizável**: Aplique diferentes taxas após as parcelas sem juros
- ✅ **Valor mínimo da parcela**: Evita exibir parcelas abaixo de um valor definido
- ✅ **Exibição em múltiplos locais**: Página do produto, galeria (com ícones independentes), carrinho e checkout
- ✅ **Tabela de parcelas**: Accordion, popup ou sempre aberta, sem loops infinitos
- ✅ **Posicionamento dinâmico**: Sempre acima do botão comprar, respeitando prioridades configuráveis

### 🎨 Configurações e Design
- ✅ **Painel em abas com UX otimizada**: PIX, Parcelas, Design PIX, Design Parcelas e Avançado
- ✅ **Preview em tempo real**: Veja alterações instantaneamente
- ✅ **Upload de ícones personalizados**: Ícones distintos para página do produto e galeria (PIX e cartão)
- ✅ **Interface visual de espaçamento**: Configure margin, padding e border-radius (produto e galeria) com suporte a valores negativos
- ✅ **Opções avançadas**: Fundo transparente, remoção de borda, ocultar ícones, remover hover e escolher ordem entre PIX e parcelas
- ✅ **Exportar/Importar + Reset**: Faça backup das configurações ou restaure o padrão com um clique

### 🔒 Segurança e Compatibilidade
- ✅ **Segurança robusta**: Nonces, sanitização, validação e checagem de permissões
- ✅ **Responsivo**: Interface adaptada para dispositivos móveis
- ✅ **Internacionalização**: Preparado para tradução
- ✅ **Compatível com HPOS**: Suporte completo ao High-Performance Order Storage do WooCommerce
- ✅ **Compatível com Elementor**: Hooks dedicados, shortcode e prevenção de duplicidade
- ✅ **Código limpo**: Seguindo padrões WordPress e WooCommerce

## Requisitos

- WordPress 5.0 ou superior
- WooCommerce 5.0 ou superior
- PHP 7.4 ou superior

## Instalação

1. Faça o upload do plugin para a pasta `/wp-content/plugins/dw-parcelas-customizadas-woo/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Certifique-se de que o WooCommerce está instalado e ativo
4. Acesse **WooCommerce > Parcelas e PIX** para configurar o plugin

## Como usar

### Configurações Globais

1. Acesse **WooCommerce > Parcelas e PIX**
2. Configure o desconto global PIX (opcional)
3. Ative/desative a exibição de parcelas
4. Configure o parcelamento (máximo de parcelas, parcelas sem juros, taxa de juros)
5. Personalize cores, ícones e textos
6. Salve as alterações

### Configurando preços PIX por produto

1. Vá para **Produtos > Todos os Produtos**
2. Edite o produto desejado
3. Na aba **Dados do produto**, role até a seção **Preço**
4. Preencha o campo **"Preço no PIX (R$)"** com o valor desejado
5. Salve o produto

### Para produtos com variações

1. Edite o produto com variações
2. Vá para a aba **Variações**
3. Para cada variação, preencha o campo **"Preço PIX (R$)"**
4. Salve as alterações

**Funcionalidades especiais para produtos variáveis:**
- ✅ **Atualização dinâmica**: O preço PIX e as parcelas são atualizados automaticamente quando o cliente seleciona uma variação
- ✅ **Prioridade de variação**: Se uma variação tem preço PIX configurado, ele será usado em vez do preço do produto principal
- ✅ **Fallback inteligente**: Se a variação não tem preço PIX, usa o preço do produto principal ou desconto global
- ✅ **Interface responsiva**: Funciona perfeitamente em dispositivos móveis

### Configurando Parcelas

As parcelas são calculadas automaticamente baseadas nas configurações globais:
- **Máximo de parcelas**: Até 12x
- **Parcelas sem juros**: Configure quantas parcelas não terão juros
- **Taxa de juros**: Defina a taxa para parcelas com juros
- **Valor mínimo**: Parcelas abaixo deste valor não serão exibidas

### Personalização do Design

Na página **WooCommerce > Parcelas e PIX**, você pode personalizar:

**Design PIX:**
- Cores (fundo, borda, texto, preço) + opção de fundo transparente e remover borda
- Ícones personalizados para página do produto e galeria
- Texto customizado e posicionamento (acima/abaixo das parcelas)
- Estilo e espessura da borda, além da remoção do hover
- Tamanho da fonte, espaçamento visual (margin/padding) e border-radius com interface gráfica
- Exibição opcional de ícones na galeria e na página do produto

**Design Parcelas:**
- Cores (fundo, borda, texto) e controle de ícones (produto/galeria)
- Posição do ícone (antes/depois do texto) e possibilidade de ocultá-lo
- Tipo de exibição da tabela (accordion, popup ou sempre aberta)
- Locais de exibição (produto, galeria, carrinho, checkout) com prioridades ajustadas
- Visual CSS para margin/padding/border-radius distintos por contexto

### Visualização no frontend

- **PIX**: Exibido na página do produto, galeria, carrinho e checkout
- **Parcelas**: Resumo da melhor condição + tabela completa de parcelas
- **Produtos variáveis**: Atualização automática ao selecionar variação
- **Carrinho/Checkout**: Preços atualizados conforme forma de pagamento

## Estrutura do Plugin

```
dw-parcelas-customizadas-woo/
├── dw-parcelas-pix-woocommerce.php    # Arquivo principal
├── includes/                           # Classes principais
│   ├── class-dw-pix-core.php          # Funcionalidades PIX
│   ├── class-dw-parcelas-installments-core.php  # Cálculo de parcelas
│   ├── class-dw-pix-admin.php         # Interface administrativa
│   ├── class-dw-pix-frontend.php      # Frontend PIX
│   ├── class-dw-parcelas-frontend.php # Frontend Parcelas
│   ├── class-dw-pix-settings.php      # Configurações
│   ├── class-dw-parcelas-config.php   # Configurações gerais
│   ├── class-dw-parcelas-hpos.php     # Compatibilidade HPOS
│   └── class-dw-pix-security.php      # Segurança
├── assets/                             # Recursos estáticos
│   ├── css/
│   │   ├── admin.css                  # Estilos admin
│   │   └── frontend.css               # Estilos frontend
│   ├── js/
│   │   ├── admin.js                   # Scripts admin
│   │   ├── frontend.js                # Scripts frontend
│   │   └── variable-products.js       # Produtos variáveis
│   └── images/
│       ├── pix-svgrepo-com.svg       # Ícone PIX padrão
│       └── credit-card.svg            # Ícone cartão padrão
├── languages/                          # Arquivos de tradução
│   └── dw-price-to-pix.pot           # Template de tradução
├── composer.json                       # Dependências PHP
└── README.md                          # Este arquivo
```

## Segurança

O plugin implementa várias camadas de segurança:

- ✅ **Sanitização de dados**: Todos os inputs são sanitizados antes do processamento
- ✅ **Verificação de nonce**: Proteção contra ataques CSRF
- ✅ **Verificação de permissões**: Apenas usuários com permissões adequadas podem editar
- ✅ **Validação de dados**: Valores são validados antes do armazenamento no banco
- ✅ **Escape de saída**: Todas as saídas HTML são escapadas adequadamente (esc_html, esc_attr, esc_url)
- ✅ **Prevenção de acesso direto**: Arquivos verificam ABSPATH
- ✅ **Classe de segurança dedicada**: Centraliza funcionalidades de segurança
- ✅ **Logs de debug seguros**: Apenas em modo WP_DEBUG

## Compatibilidade

- **WooCommerce**: 5.0+
- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Gateways PIX**: Compatível com a maioria dos gateways que incluem "PIX" no nome
- **HPOS**: Totalmente compatível com o Armazenamento de pedidos de alto desempenho

### HPOS (High-Performance Order Storage)

O plugin é **100% compatível** com o HPOS do WooCommerce, que melhora significativamente a performance em lojas com muitos pedidos. O plugin:

- ✅ Declara compatibilidade automaticamente
- ✅ Usa APIs compatíveis com HPOS
- ✅ Funciona tanto com HPOS ativo quanto inativo
- ✅ Não interfere no funcionamento do sistema de pedidos
- ✅ Mantém todas as funcionalidades independente do status do HPOS

## Destaques da versão 0.1.0

- 🔁 **Arquitetura unificada**: PIX e Parcelas funcionando juntos com prioridades dinâmicas que garantem exibição acima do botão comprar (inclusive no Elementor).
- 🧭 **Posicionamento controlado**: PIX pode aparecer acima/abaixo das parcelas e respeita os hooks adicionais `woocommerce_before_add_to_cart_form/button`.
- 🖼 **Visual Builder**: Interface amigável para margin/padding/border-radius por contexto, aceitando valores negativos para margin.
- 🧩 **Elementor & Shortcodes**: Compatibilidade nativa com builders e shortcode `[dw_pix_price]` para posicionamento manual.
- 💾 **Exportar/Importar/Reset**: Fluxo seguro com validações, feedback visual e botões exclusivos na aba Avançado.
- 🎛 **Design independente**: Ícones separados para página do produto e galeria, opção de esconder ícones, remover hover e manter a cor original dos SVGs.
- 🛡 **Sanitização inteligente**: Salvamento por abas sem sobrescrever campos não enviados e hidden inputs para garantir o estado real dos checkboxes.
- 📊 **Tabela aprimorada**: Modos accordion/popup/sempre aberta sem loops infinitos, com prevenção de múltiplas renderizações.

## Suporte

Para suporte, reporte bugs ou solicite funcionalidades, visite:
- [GitHub Issues](https://github.com/agenciadw/dw-parcelas-pix-woocommerce/issues)
- [GitHub Repository](https://github.com/agenciadw/dw-parcelas-pix-woocommerce)

## Changelog

### 0.1.0 (Lançamento)
- Integração completa PIX + Parcelas com posicionamento dinâmico e compatibilidade Elementor
- Painel em abas com UX aprimorada, botões padronizados e visual builder de espaçamentos
- Ícones independentes para página do produto e galeria, opções de ocultar/mostrar e preservar cor original
- Shortcode `[dw_pix_price]`, hooks adicionais e prevenção de renderizações duplicadas
- Exportar/Importar/Reset com validações, mensagens e segurança reforçada
- Salva configurações por aba sem sobrescrever dados de outras seções
- Melhorias gerais de CSS/JS (removido hover, sem loops, sem fontes fixas) e revisão completa de segurança

## Licença

Este plugin está licenciado sob a GPL v2 ou posterior.

## Autor

**David William da Costa**
- GitHub: [@agenciadw](https://github.com/agenciadw)
- Site: [DW Digital](https://github.com/agenciadw)

---

Desenvolvido com ❤️ para a comunidade WooCommerce brasileira.
