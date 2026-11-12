<?php
/** Incoming stock is informative; it is never sellable stock. */
declare(strict_types=1);
namespace Ellamara\Catalog;
defined('ABSPATH') || exit;

function is_coming_soon(\WC_Product $product): bool {
    return !$product->is_in_stock() && $product->get_meta('_ellamara_availability') === 'proximamente';
}

add_filter('ellamara_catalog_unavailable_label', static function (string $label, \WC_Product $product): string {
    return is_coming_soon($product) ? 'Próximamente' : $label;
}, 10, 2);

add_filter('woocommerce_get_availability_text', static function (string $text, \WC_Product $product): string {
    return is_coming_soon($product) ? 'Próximamente. Todavía no disponible para compra.' : $text;
}, 20, 2);

add_action('woocommerce_product_options_inventory_product_data', static function (): void {
    woocommerce_wp_select([
        'id' => '_ellamara_availability', 'label' => 'Disponibilidad Ellamara',
        'options' => ['' => 'Según inventario', 'proximamente' => 'Próximamente'],
        'description' => 'Próximamente se muestra mientras no haya stock vendible. No habilita reservas ni compras.',
        'desc_tip' => true,
    ]);
    woocommerce_wp_text_input([
        'id' => '_ellamara_incoming_quantity', 'label' => 'Unidades por recibir', 'type' => 'number',
        'custom_attributes' => ['min' => '0', 'step' => '1'],
        'description' => 'Control interno. No se suma a las existencias disponibles.', 'desc_tip' => true,
    ]);
});

add_action('woocommerce_admin_process_product_object', static function (\WC_Product $product): void {
    if (isset($_POST['_ellamara_availability']) && is_string($_POST['_ellamara_availability'])) {
        $product->update_meta_data('_ellamara_availability', $_POST['_ellamara_availability'] === 'proximamente' ? 'proximamente' : '');
    }
    if (isset($_POST['_ellamara_incoming_quantity']) && is_scalar($_POST['_ellamara_incoming_quantity'])) {
        $product->update_meta_data('_ellamara_incoming_quantity', max(0, (int) $_POST['_ellamara_incoming_quantity']));
    }
});
