<?php
/**
 * Plugin Name: Ellamara · Controles de carrito
 * Description: Contadores de cantidad accesibles en el catálogo usando las APIs nativas de WooCommerce.
 * Version: 0.3.8
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 * Text Domain: ellamara-cart
 */
declare(strict_types=1);

namespace Ellamara\Cart;

defined('ABSPATH') || exit;

/** Enhances simple-product catalog buttons; native variation and product forms remain intact. */
final class CatalogControls
{
    public function register(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_filter('render_block_woocommerce/product-button', [$this, 'render'], 20, 3);
        add_filter('woocommerce_loop_add_to_cart_link', [$this, 'renderClassic'], 20, 2);
    }

    public function assets(): void
    {
        if (!function_exists('WC')) {
            return;
        }
        $base = plugin_dir_url(__FILE__);
        wp_enqueue_style('ellamara-cart-controls', $base . 'assets/controls.css', [], (string) filemtime(__DIR__ . '/assets/controls.css'));
        wp_enqueue_script('ellamara-cart-controls', $base . 'assets/controls.js', ['wp-data', 'wc-blocks-data-store'], (string) filemtime(__DIR__ . '/assets/controls.js'), true);
    }

    public function render(string $html, array $attributes, \WP_Block $block): string
    {
        // Never replace the submit button inside a product's options form.
        if (!empty($block->context['woocommerce/isDescendantOfAddToCartWithOptions'])) {
            return $html;
        }
        $id = (int) ($block->context['postId'] ?? $attributes['productId'] ?? 0);
        return $this->control($html, wc_get_product($id));
    }

    public function renderClassic(string $html, \WC_Product $product): string
    {
        return $this->control($html, $product);
    }

    private function control(string $html, $product): string
    {
        // Block rendering can also invoke the classic loop filter; enhance only once.
        if (str_contains($html, 'class="ellamara-purchase')) {
            return $html;
        }
        if (!$product instanceof \WC_Product || !$product->is_type('simple') || !$product->is_purchasable() || !$product->is_in_stock()) {
            return $html;
        }
        $id = $product->get_id();
        $name = $product->get_name();
        $max = $product->is_sold_individually() ? 1 : $product->get_max_purchase_quantity();
        $add = sprintf(__('Añadir %s al carrito', 'ellamara-cart'), $name);
        $less = sprintf(__('Quitar una unidad de %s', 'ellamara-cart'), $name);
        $more = sprintf(__('Añadir una unidad de %s', 'ellamara-cart'), $name);
        return sprintf(
            '<div class="ellamara-purchase" data-product-id="%d" data-max="%d" data-product-name="%s">'
            . '<div class="ellamara-native-button">%s</div>'
            . '<div class="ellamara-cart-control" hidden>'
            . '<button type="button" class="ellamara-add" data-cart-action="add" aria-label="%s"><span aria-hidden="true">＋</span> %s</button>'
            . '<div class="ellamara-in-cart" hidden><span class="ellamara-in-cart-label"><span aria-hidden="true">✓</span> En tu carrito</span>'
            . '<div class="ellamara-stepper" role="group" aria-label="%s" hidden>'
            . '<button type="button" data-cart-action="decrease" aria-label="%s">−</button>'
            . '<output class="ellamara-quantity" aria-label="%s">0</output>'
            . '<button type="button" data-cart-action="increase" aria-label="%s">+</button></div></div>'
            . '<p class="ellamara-cart-status" role="status" aria-live="polite" aria-atomic="true"></p></div></div>',
            $id, $max, esc_attr($name), $html, esc_attr($add), esc_html__('Añadir al carrito', 'ellamara-cart'),
            esc_attr(sprintf(__('Cantidad de %s en el carrito', 'ellamara-cart'), $name)), esc_attr($less),
            esc_attr__('Unidades en el carrito', 'ellamara-cart'), esc_attr($more)
        );
    }
}

add_action('plugins_loaded', static function (): void {
    if (class_exists('WooCommerce')) {
        (new CatalogControls())->register();
    }
});

require_once __DIR__ . '/pdp.php';


