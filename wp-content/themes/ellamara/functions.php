<?php
/** Presentation only. Commerce belongs to WooCommerce and business plugins. */
declare(strict_types=1);

namespace Ellamara\Theme;

defined('ABSPATH') || exit;
require_once __DIR__ . '/account.php';
require_once __DIR__ . '/usability.php';
require_once __DIR__ . '/product-presentation.php';
require_once __DIR__ . '/support.php';

add_action('after_setup_theme', static function (): void {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/site.css');
    add_editor_style('assets/css/commerce.css');
    add_editor_style('assets/css/navigation.css');
    add_editor_style('assets/css/footer.css');
    add_editor_style('assets/css/experience.css');
    add_editor_style('assets/css/layout.css');
    add_editor_style('assets/css/catalog-cards.css');
    load_theme_textdomain('ellamara', get_template_directory() . '/languages');
});

add_action('wp_enqueue_scripts', static function (): void {
    $path = '/assets/css/site.css';
    wp_enqueue_style('ellamara-site', get_theme_file_uri($path), [], (string) filemtime(get_theme_file_path($path)));
    $commerce = '/assets/css/commerce.css';
    wp_enqueue_style('ellamara-commerce', get_theme_file_uri($commerce), ['ellamara-site'], (string) filemtime(get_theme_file_path($commerce)));
    wp_enqueue_style('ellamara-navigation', get_theme_file_uri('/assets/css/navigation.css'), ['ellamara-commerce'], (string) filemtime(get_theme_file_path('/assets/css/navigation.css')));
    wp_enqueue_style('ellamara-footer', get_theme_file_uri('/assets/css/footer.css'), ['ellamara-commerce'], (string) filemtime(get_theme_file_path('/assets/css/footer.css')));
    wp_enqueue_style('ellamara-experience', get_theme_file_uri('/assets/css/experience.css'), ['ellamara-footer', 'ellamara-navigation'], (string) filemtime(get_theme_file_path('/assets/css/experience.css')));
    wp_enqueue_style('ellamara-story', get_theme_file_uri('/assets/css/story.css'), ['ellamara-experience'], (string) filemtime(get_theme_file_path('/assets/css/story.css')));
    if (is_page(['preguntas-frecuentes', 'envios'])) {
        wp_enqueue_style('ellamara-faq', get_theme_file_uri('/assets/css/faq.css'), ['ellamara-experience'], (string) filemtime(get_theme_file_path('/assets/css/faq.css')));
    }
    if (is_404()) {
        wp_enqueue_style('ellamara-not-found', get_theme_file_uri('/assets/css/not-found.css'), ['ellamara-experience'], (string) filemtime(get_theme_file_path('/assets/css/not-found.css')));
    }
    if (function_exists('is_account_page') && is_account_page()) {
        wp_enqueue_style('ellamara-account', get_theme_file_uri('/assets/css/account.css'), ['ellamara-experience'], (string) filemtime(get_theme_file_path('/assets/css/account.css')));
    }
    wp_enqueue_style('ellamara-cards', get_theme_file_uri('/assets/css/catalog-cards.css'), ['ellamara-experience'], (string) filemtime(get_theme_file_path('/assets/css/catalog-cards.css')));
    if (function_exists('is_product') && is_product()) {
        wp_enqueue_style('ellamara-product', get_theme_file_uri('/assets/css/product.css'), ['ellamara-cards'], (string) filemtime(get_theme_file_path('/assets/css/product.css')));
        wp_enqueue_script('ellamara-product', get_theme_file_uri('/assets/js/product.js'), [], (string) filemtime(get_theme_file_path('/assets/js/product.js')), true);
    }
    wp_enqueue_script('ellamara-navigation', get_theme_file_uri('/assets/js/navigation.js'), [], (string) filemtime(get_theme_file_path('/assets/js/navigation.js')), true);
});

// Presentation of WooCommerce's existing cart and checkout; native forms stay intact.
add_action('wp_enqueue_scripts',static function():void {
    $path='/assets/css/ui-refinements.css';
    wp_enqueue_style('ellamara-ui-refinements',get_theme_file_uri($path),['ellamara-experience'],(string)filemtime(get_theme_file_path($path)));
},40);
add_filter('render_block', static function (string $html, array $block): string {
    $name = $block['blockName'] ?? '';
    if (!in_array($name, ['woocommerce/cart', 'woocommerce/checkout'], true) || !function_exists('wc_get_page_permalink') || is_order_received_page()) {
        return $html;
    }
    $checkout = $name === 'woocommerce/checkout';
    $steps = '<nav class="ellamara-purchase-steps alignwide" aria-label="Pasos de compra"><a href="' . esc_url(wc_get_page_permalink('cart')) . '"' . (!$checkout ? ' aria-current="step"' : '') . '>1. Carrito</a><span aria-hidden="true">→</span><span' . ($checkout ? ' aria-current="step"' : '') . '>2. Datos y pago</span></nav>';
    $back = '<p class="ellamara-continue alignwide"><a href="' . esc_url(wc_get_page_permalink('shop')) . '">← Seguir explorando la tienda</a></p>';
    $title = $checkout ? '<h1 class="ellamara-checkout-title">' . esc_html__('Finalizar compra', 'ellamara') . '</h1>' : '';
    $international = '<details class="ellamara-international-shipping alignwide"><summary>¿Tu pedido es para otro país? <span>Consulta el envío internacional</span></summary><div class="ellamara-international-content"><p>Escríbenos antes de comprar para consultar cobertura, costo y condiciones del envío internacional.</p><a href="https://wa.me/message/3LIIIQSYBGX5G1" target="_blank" rel="noopener noreferrer">Consultar por WhatsApp <span aria-hidden="true">↗</span></a></div></details>';
    return $title . $steps . (!$checkout ? $back : '') . $international . $html;
}, 20, 2);

add_action('woocommerce_after_add_to_cart_form', static function (): void {
    if (!is_product()) { return; }
    echo '<nav class="ellamara-product-help" aria-label="Ayuda con este producto">';
    foreach (['envios' => 'Envíos y entregas', 'cambios-y-garantias' => 'Cambios y garantías'] as $slug => $label) {
        $page = get_page_by_path($slug);
        if ($page && $page->post_status === 'publish') {
            echo '<a href="' . esc_url(get_permalink($page)) . '">' . esc_html($label) . '</a>';
        }
    }
    echo '</nav>';
});

add_action('init', static function (): void {
    register_block_pattern_category('ellamara', ['label' => __('Ellamara · Secciones', 'ellamara')]);
});

// Two compact rows on desktop, with native WooCommerce pagination and sorting.
add_filter('loop_shop_per_page', static fn (): int => 8, 20);
add_filter('loop_shop_columns', static fn (): int => 4, 20);

// Keep WordPress's accessible dialog, keyboard handling and focus restoration.
add_filter('render_block_core/navigation', static function (string $html, array $block): string {
    if (!in_array('ellamara-menu', explode(' ', $block['attrs']['className'] ?? ''), true)) {
        return $html;
    }
    $heading = '<div class="ellamara-menu-heading"><p class="ellamara-menu-eyebrow">'
        . esc_html__('El capítulo oculto', 'ellamara') . '</p><p class="ellamara-menu-title">'
        . esc_html__('Explora Ellamara', 'ellamara') . '</p><p class="ellamara-menu-description">'
        . esc_html__('Encuentra tu próximo tesoro entre páginas.', 'ellamara') . '</p></div>';
    return preg_replace(
        '/(<div\b[^>]*class="[^"]*\bwp-block-navigation__responsive-container-content\b[^"]*"[^>]*>)/',
        '$1' . $heading,
        $html,
        1
    ) ?? $html;
}, 20, 2);

add_action('wp_enqueue_scripts', static function (): void {
    $dependencies = ['ellamara-experience'];
    foreach (['ellamara-account', 'ellamara-contact'] as $handle) {
        if (wp_style_is($handle, 'enqueued')) { $dependencies[] = $handle; }
    }
    wp_enqueue_style('ellamara-layout', get_theme_file_uri('/assets/css/layout.css'), $dependencies, (string) filemtime(get_theme_file_path('/assets/css/layout.css')));
}, 100);
