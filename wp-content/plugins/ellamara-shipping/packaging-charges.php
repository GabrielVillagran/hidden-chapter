<?php
/** A separate protective package per book; never estimates freight or multi-book capacity. */
declare(strict_types=1);
namespace Ellamara\Shipping;
defined('ABSPATH') || exit;

function book_package(\WC_Product $product): ?string {
    $isbn = trim((string)$product->get_meta('_ellamara_book_isbn'));
    if ($isbn === '' && !has_term('libros', 'product_cat', $product->get_id())) { return null; }
    $small = ['9789566180746','9786287624450','9786287624115','9789585573772','9786287844537','9786287732230','9786287574953','9786287732247'];
    return in_array($isbn, $small, true) ? 'small' : 'large';
}

add_filter('render_block', static function(string $html, array $block): string {
    if (!in_array($block['blockName'] ?? '', ['woocommerce/cart-totals-block','woocommerce/checkout-order-summary-block'], true) || is_order_received_page()) { return $html; }
    return $html . '<aside class="ellamara-shipping-pending" role="note"><strong>Envío: pendiente de cotización</strong><p>Te confirmaremos el costo del transporte antes del pago.</p></aside>';
}, 35, 2);
