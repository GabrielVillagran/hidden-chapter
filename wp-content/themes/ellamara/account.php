<?php
/** Account presentation uses WooCommerce endpoints and leaves authentication intact. */
declare(strict_types=1);
namespace Ellamara\Theme;
defined('ABSPATH') || exit;

add_action('woocommerce_before_customer_login_form', static function (): void {
    if (is_user_logged_in()) { return; }
    echo '<aside class="ellamara-guest-guide" aria-label="Comprar sin cuenta"><h2>¿Es tu primera compra?</h2><p>Puedes comprar sin cuenta. Añade tus productos al carrito y continúa con tus datos de envío y pago.</p><a href="' . esc_url(wc_get_page_permalink('shop')) . '">Volver a la tienda <span aria-hidden="true">→</span></a></aside>';
});

add_filter('woocommerce_account_menu_items', static function (array $items): array {
    unset($items['downloads']);
    if (isset($items['dashboard'])) { $items['dashboard'] = 'Resumen'; }
    if (isset($items['edit-account'])) { $items['edit-account'] = 'Mis datos'; }
    if (isset($items['customer-logout'])) { $items['customer-logout'] = 'Cerrar sesión'; }
    return $items;
});

add_action('template_redirect', static function (): void {
    if (function_exists('is_account_page') && is_account_page() && is_wc_endpoint_url('downloads')) {
        wp_safe_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
});

function account_contact_url(): string {
    $page = get_page_by_path('contacto');
    return $page && $page->post_status === 'publish' ? get_permalink($page) : home_url('/contacto/');
}

function account_icon(string $endpoint): string {
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'orders' => '<path d="m3 7 9-4 9 4v10l-9 4-9-4V7Zm0 0 9 4 9-4M12 11v10M7.5 5l9 4"/>',
        'edit-address' => '<path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
        'edit-account' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'customer-logout' => '<path d="M9 4H4v16h5M14 8l4 4-4 4M8 12h13"/>',
    ];
    return '<svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$endpoint] ?? $paths['dashboard']) . '</svg>';
}


add_action('woocommerce_edit_account_form_start', static function (): void {
    echo '<header class="ellamara-account-form-heading"><h2>Datos personales</h2><p>Actualiza tu nombre y el correo donde recibirás información sobre tus pedidos. Los campos con * son obligatorios.</p></header>';
});
add_action('woocommerce_edit_account_form_fields', static function (): void {
    echo '<p class="ellamara-account-password-guide" id="ellamara-password-guide">Para conservar tu contraseña, deja vacíos los campos de cambio de contraseña.</p>';
});
