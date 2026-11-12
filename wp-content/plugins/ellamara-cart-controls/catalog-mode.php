<?php
/** Public browsing while online orders are paused. */
declare(strict_types=1);

namespace Ellamara\Cart;

defined('ABSPATH') || exit;

const CATALOG_ONLY = true;
const CATALOG_MESSAGE = 'Estamos trabajando para brindarte una mejor experiencia. Pronto podrás comprar en nuestra página web.';

function catalog_only(): bool {
    return CATALOG_ONLY && class_exists('WooCommerce');
}

add_action('plugins_loaded', static function (): void {
    if (!catalog_only()) {
        return;
    }

    // Override storefront visibility at runtime. The saved setting is kept intact.
    add_filter('pre_option_woocommerce_coming_soon', static fn (): string => 'no');
    add_filter('pre_option_woocommerce_enable_myaccount_registration', static fn (): string => 'no');
    add_filter('pre_option_users_can_register', static fn (): string => '0');

    add_filter('woocommerce_is_purchasable', '__return_false', PHP_INT_MAX);
    add_filter('woocommerce_variation_is_purchasable', '__return_false', PHP_INT_MAX);
    add_filter('woocommerce_loop_add_to_cart_link', '__return_empty_string', PHP_INT_MAX);
    add_action('woocommerce_checkout_process', static function (): void {
        wc_add_notice(CATALOG_MESSAGE, 'error');
    });

    foreach (['mini-cart', 'customer-account', 'product-button', 'add-to-cart-form', 'add-to-cart-with-options'] as $block) {
        add_filter('render_block_woocommerce/' . $block, '__return_empty_string', PHP_INT_MAX);
    }

    add_action('template_redirect', static function (): void {
        if (is_cart() || is_checkout() || is_account_page()) {
            nocache_headers();
            wp_safe_redirect(wc_get_page_permalink('shop'), 302);
            exit;
        }
    }, 0);

    add_filter('rest_pre_dispatch', static function ($result, \WP_REST_Server $server, \WP_REST_Request $request) {
        if (preg_match('~^/wc/store/(?:v\d+/)?(?:cart|checkout)(?:/|$)~', $request->get_route())) {
            return new \WP_Error('ellamara_catalog_only', CATALOG_MESSAGE, ['status' => 403]);
        }
        return $result;
    }, 10, 3);
});
