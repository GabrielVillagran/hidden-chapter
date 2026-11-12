<?php
/** Product presentation; native WooCommerce forms and inventory remain authoritative. */
declare(strict_types=1);
namespace Ellamara\Theme;
defined('ABSPATH') || exit;

function product_intro(string $html, array $attributes, \WP_Block $block): string {
    if (!is_product()) { return $html; }
    $id = (int) ($block->context['postId'] ?? get_queried_object_id());
    if ($id !== get_queried_object_id()) { return $html; }
    $product = wc_get_product($id);
    if (!$product) { return $html; }
    $author = (string) $product->get_meta('_ellamara_book_author');
    $format = (string) $product->get_meta('_ellamara_book_format');
    if (!$author && !$format) { return $html; }
    $generated = $author . '. ' . $format . '.';
    $description = trim(wp_strip_all_tags($product->get_short_description()));
    // Remove only the exact import-generated sentence; preserve authored summaries.
    if (in_array($description, [$generated, $generated . ' Próximamente: todavía no disponible para compra.'], true)) { $html = ''; }
    $facts = '<dl class="ellamara-product-facts">';
    foreach (['Encuadernación' => $format] as $label => $value) {
        if ($value !== '') { $facts .= '<div><dt>' . esc_html($label) . '</dt><dd>' . esc_html($value) . '</dd></div>'; }
    }
    return $facts . '</dl>' . $html;
}
add_filter('render_block_core/post-excerpt', __NAMESPACE__ . '\\product_intro', 30, 3);
add_filter('render_block_woocommerce/product-summary', __NAMESPACE__ . '\\product_intro', 30, 3);

add_filter('render_block_woocommerce/product-price', static function (string $html, array $attributes, \WP_Block $block): string {
    if (!is_product()) { return $html; }
    $id = (int) ($block->context['postId'] ?? get_queried_object_id());
    if ($id !== get_queried_object_id() || !has_term('marcapaginas', 'product_cat', $id)) { return $html; }

    $product = wc_get_product($id);
    $pack_price = $product ? $product->get_meta('_ellamara_three_pack_price') : '';
    if (!is_numeric($pack_price) || (float) $pack_price <= 0 || $product->get_price() === '') { return $html; }

    return '<div class="ellamara-bookmark-prices" aria-label="Precios de marcapáginas">'
        . '<div><span>Por unidad</span>' . $html . '</div>'
        . '<div><span>Oferta de 3</span><strong>' . wp_kses_post(wc_price((float) $pack_price)) . '</strong></div>'
        . '</div>';
}, 30, 3);

add_filter('render_block_core/post-title', static function (string $html, array $block, \WP_Block $instance): string {
    if (!is_product() || (int) ($instance->context['postId'] ?? 0) !== get_queried_object_id()) { return $html; }
    $product = wc_get_product(get_queried_object_id());
    $author = $product ? $product->get_meta('_ellamara_book_author') : '';
    return $author ? $html . '<p class="ellamara-product-author">por ' . esc_html($author) . '</p>' : $html;
}, 30, 3);

add_filter('render_block_woocommerce/product-meta', static function (string $html): string {
    return is_product() ? '' : $html;
});

add_filter('woocommerce_get_availability_text', static function (string $text, \WC_Product $product): string {
    if ($product->is_in_stock() && $product->managing_stock() && !$product->is_on_backorder(1) && (int) $product->get_stock_quantity() === 1) {
        return 'Último ejemplar disponible';
    }
    return $text;
}, 30, 2);

add_filter('woocommerce_product_review_comment_form_args', static function (array $args): array {
    $args['label_submit'] = 'Enviar valoración';
    $args['title_reply'] = 'Comparte tu experiencia';
    $args['title_reply_to'] = 'Comparte tu experiencia';
    return $args;
});

add_filter('render_block_woocommerce/product-collection', static function (string $html, array $block): string {
    if (!is_product() || ($block['attrs']['collection'] ?? '') !== 'woocommerce/product-collection/related') { return $html; }
    return '<section class="ellamara-related" aria-label="Productos relacionados" aria-roledescription="carrusel">'
        . '<div class="ellamara-carousel-controls" hidden><button type="button" data-carousel-prev aria-label="Ver productos relacionados anteriores">←</button>'
        . '<button type="button" data-carousel-next aria-label="Ver más productos relacionados">→</button></div>'
        . $html . '<p class="ellamara-carousel-hint">Desliza para explorar más productos.</p></section>';
}, 30, 2);

add_filter('render_block_core/post-terms', static function (string $html, array $block): string {
    if (is_product() && ($block['attrs']['term'] ?? '') === 'product_cat') {
        return str_replace('Category:', 'Categoría:', $html);
    }
    return $html;
}, 20, 2);
