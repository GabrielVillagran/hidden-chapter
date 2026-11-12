<?php
/** Small presentation improvements for recovery, search and catalog accessibility. */
declare(strict_types=1);

namespace Ellamara\Theme;

defined('ABSPATH') || exit;

// WooCommerce's secondary empty-results search can render an empty post_type.
// Keep it scoped to products, including the recovery screen's search.
add_filter('render_block_core/search', static function (string $html): string {
    if (!is_404() && !(is_search() && get_query_var('post_type') === 'product')) {
        return $html;
    }
    $processor = new \WP_HTML_Tag_Processor($html);
    while ($processor->next_tag('INPUT')) {
        if ($processor->get_attribute('name') === 'post_type') {
            $processor->set_attribute('value', 'product');
            return $processor->get_updated_html();
        }
    }
    return $html;
});

// A placeholder image must still identify the product to assistive technology.
add_filter('render_block_woocommerce/product-image', static function (string $html, array $attributes, \WP_Block $block): string {
    $product = wc_get_product((int) ($block->context['postId'] ?? $attributes['productId'] ?? 0));
    if (!$product instanceof \WC_Product) { return $html; }
    $processor = new \WP_HTML_Tag_Processor($html);
    while ($processor->next_tag('A')) {
        $processor->set_attribute('aria-label', $product->get_name());
    }
    return $processor->get_updated_html();
}, 20, 3);

add_filter('woocommerce_product_get_image', static function (string $html, \WC_Product $product): string {
    if ($product->get_image_id()) { return $html; }
    $processor = new \WP_HTML_Tag_Processor($html);
    if ($processor->next_tag('IMG')) {
        $processor->set_attribute('alt', $product->get_name() . ' — imagen pendiente');
    }
    return $processor->get_updated_html();
}, 20, 2);

// Show availability before the visitor opens a product that cannot be bought.
add_filter('render_block_woocommerce/product-button', static function (string $html, array $attributes, \WP_Block $block): string {
    $product = wc_get_product((int) ($block->context['postId'] ?? $attributes['productId'] ?? 0));
    if ($product instanceof \WC_Product && !$product->is_in_stock() && !str_contains($html, 'ellamara-stock-label')) {
        return '<p class="ellamara-stock-label">' . esc_html(apply_filters('ellamara_catalog_unavailable_label', __('Agotado por ahora', 'ellamara'), $product)) . '</p>' . $html;
    }
    return $html;
}, 30, 3);

add_filter('woocommerce_loop_add_to_cart_link', static function (string $html, \WC_Product $product): string {
    if (!$product->is_in_stock()) {
        return '<p class="ellamara-stock-label">' . esc_html(apply_filters('ellamara_catalog_unavailable_label', __('Agotado por ahora', 'ellamara'), $product)) . '</p>' . $html;
    }
    return $html;
}, 30, 2);

add_filter('woocommerce_product_add_to_cart_text', static function (string $text, \WC_Product $product): string {
    return $product->is_in_stock() ? $text : __('Ver producto', 'ellamara');
}, 20, 2);

add_filter('woocommerce_product_add_to_cart_description', static function (string $text, \WC_Product $product): string {
    return $product->is_in_stock() ? $text : sprintf(__('Ver %1$s: %2$s', 'ellamara'), $product->get_name(), apply_filters('ellamara_catalog_unavailable_label', __('Agotado por ahora', 'ellamara'), $product));
}, 20, 2);
