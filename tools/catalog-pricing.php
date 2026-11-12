<?php
/** Run with wp eval-file. The default mode only reports the proposed updates. */
declare(strict_types=1);

if (PHP_SAPI !== 'cli' || !defined('ABSPATH')) { exit(1); }

$apply = ($args[0] ?? '') === 'apply';
$price_marker = '_ellamara_packaging_included';
$upcoming_sku = 'EL-9788419822178-TB';
$upcoming_id = wc_get_product_id_by_sku($upcoming_sku);
if (!$upcoming_id || get_woocommerce_currency() !== 'COP' || !function_exists('Ellamara\\Shipping\\book_package')) {
    throw new RuntimeException('Verifica el SKU de La hipótesis del amor, la moneda COP y el plugin Ellamara · Preparación de envíos.');
}

$packages = \Ellamara\Shipping\packages();
$products = wc_get_products(['status' => 'publish', 'limit' => -1]);
$changes = [];
foreach ($products as $product) {
    $id = $product->get_id();
    $sku = $product->get_sku();
    $is_book = has_term('libros', 'product_cat', $id);
    if (!$is_book && has_term(['kits', 'kits-literarios'], 'product_cat', $id)) {
        $changes[] = ['id' => $id, 'sku' => $sku, 'status' => ['publish', 'draft']];
        continue;
    }
    if (!$is_book) { continue; }

    $size = \Ellamara\Shipping\book_package($product);
    $price = $product->get_regular_price('edit');
    $sale = $product->get_sale_price('edit');
    $charge = $packages[$size]['cost'];
    if (!$product->is_type('simple') || !is_numeric($price) || ($sale !== '' && !is_numeric($sale)) || $charge <= 0) {
        throw new RuntimeException('Revisa tipo, precio o empaque del SKU ' . $sku);
    }
    $change = ['id' => $id, 'sku' => $sku];
    if (!$product->get_meta($price_marker, true, 'edit')) {
        $change['price'] = [$price, wc_format_decimal((float) $price + $charge)];
        if ($sale !== '') {
            $change['sale'] = [$sale, wc_format_decimal((float) $sale + $charge)];
        }
    }
    if ($id === $upcoming_id && ($product->get_stock_quantity() !== 0 || $product->get_stock_status() !== 'outofstock' || $product->get_backorders() !== 'no' || $product->get_meta('_ellamara_availability') !== 'proximamente' || !$product->get_manage_stock())) {
        $change['upcoming'] = [
            'stock_before' => $product->get_stock_quantity(),
            'status_before' => $product->get_stock_status(),
            'backorders_before' => $product->get_backorders(),
            'manage_stock_before' => $product->get_manage_stock(),
            'availability_before' => $product->get_meta('_ellamara_availability'),
        ];
    }
    if (count($change) > 2) { $changes[] = $change; }
}

echo wp_json_encode($changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if (!$apply) { return; }

foreach ($changes as $change) {
    $product = wc_get_product($change['id']);
    if (!$product) { throw new RuntimeException('SKU no encontrado: ' . $change['sku']); }
    if (isset($change['status'])) { $product->set_status('draft'); }
    if (isset($change['price'])) {
        $product->set_regular_price($change['price'][1]);
        if (isset($change['sale'])) { $product->set_sale_price($change['sale'][1]); }
        $product->update_meta_data($price_marker, $change['price'][0]);
    }
    if (isset($change['upcoming'])) {
        $product->set_manage_stock(true);
        $product->set_stock_quantity(0);
        $product->set_stock_status('outofstock');
        $product->set_backorders('no');
        $product->update_meta_data('_ellamara_availability', 'proximamente');
    }
    $product->save();
}
