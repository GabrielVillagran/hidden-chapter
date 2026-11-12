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

function packaging_lines(array $items): array {
    $lines = [];
    foreach ($items as $item) {
        $product = $item['data'] ?? null;
        $quantity = (int)($item['quantity'] ?? 0);
        if (!$product instanceof \WC_Product || $quantity < 1) { continue; }
        $size = book_package($product);
        if ($size === null) { continue; }
        $id = $product->get_id();
        if (!isset($lines[$id])) { $lines[$id] = ['product'=>$product,'size'=>$size,'quantity'=>0]; }
        $lines[$id]['quantity'] += $quantity;
    }
    return $lines;
}

add_action('woocommerce_cart_calculate_fees', static function(\WC_Cart $cart): void {
    if (is_admin() && !wp_doing_ajax()) { return; }
    $packages = packages();
    foreach (packaging_lines($cart->get_cart()) as $line) {
        $package = $packages[$line['size']];
        $label = sprintf('Empaque · %s · %s × %d', $line['product']->get_name(), mb_strtolower($package['name']), $line['quantity']);
        $cart->add_fee($label, $package['cost'] * $line['quantity'], false);
    }
});

add_filter('render_block', static function(string $html, array $block): string {
    if (!in_array($block['blockName'] ?? '', ['woocommerce/cart-totals-block','woocommerce/checkout-order-summary-block'], true) || is_order_received_page()) { return $html; }
    return $html . '<aside class="ellamara-shipping-pending" role="note"><strong>Envío: pendiente de cotización</strong><p>Este total incluye productos y empaque; no incluye envío. Te confirmaremos el costo del transporte antes del pago.</p><p class="ellamara-packaging-policy">Se calcula una caja por ejemplar.</p></aside>';
}, 35, 2);
