<?php
/** Dismissible, session-scoped invitation to personalized support. */
declare(strict_types=1);
namespace Ellamara\Theme;
defined('ABSPATH') || exit;

function show_whatsapp_welcome(): bool {
    return !is_admin() && !(function_exists('is_checkout') && (is_checkout() || is_cart() || is_account_page()));
}
add_action('wp_enqueue_scripts', static function (): void {
    if (function_exists('is_cart') && is_cart()) {
        wp_enqueue_style('ellamara-whatsapp-welcome', get_theme_file_uri('/assets/css/whatsapp-welcome.css'), ['ellamara-experience'], (string) filemtime(get_theme_file_path('/assets/css/whatsapp-welcome.css')));
        wp_enqueue_script('ellamara-whatsapp-cart', get_theme_file_uri('/assets/js/whatsapp-cart.js'), [], (string) filemtime(get_theme_file_path('/assets/js/whatsapp-cart.js')), true);
    }
    if (!show_whatsapp_welcome()) { return; }
    foreach (['css', 'js'] as $type) {
        $path = '/assets/' . $type . '/whatsapp-welcome.' . $type;
        if ($type === 'css') {
            wp_enqueue_style('ellamara-whatsapp-welcome', get_theme_file_uri($path), ['ellamara-experience'], (string) filemtime(get_theme_file_path($path)));
        } else {
            wp_enqueue_script('ellamara-whatsapp-welcome', get_theme_file_uri($path), [], (string) filemtime(get_theme_file_path($path)), true);
        }
    }
});
add_action('wp_footer', static function (): void {
    if (!show_whatsapp_welcome()) { return; }
    ?>
    <dialog class="ellamara-whatsapp-welcome" id="ellamara-whatsapp-welcome" aria-labelledby="ellamara-whatsapp-title" aria-describedby="ellamara-whatsapp-description">
        <button type="button" class="ellamara-whatsapp-close" aria-label="Cerrar aviso" autofocus>×</button>
        <p class="ellamara-whatsapp-eyebrow">Estamos para ayudarte</p>
        <h2 id="ellamara-whatsapp-title">Una atención más personal</h2>
        <p id="ellamara-whatsapp-description">Para una atención personalizada, contáctanos por WhatsApp.</p>
        <p class="ellamara-whatsapp-payment"><strong>¿Prefieres pagar con Nequi, DaviPlata o PSE?</strong><br>Escríbenos por WhatsApp y te compartiremos las opciones y los pasos para realizar tu pago.</p>
        <a class="ellamara-whatsapp-link" href="https://wa.me/message/3LIIIQSYBGX5G1" target="_blank" rel="noopener noreferrer">Consultar opciones de pago <span aria-hidden="true">↗</span></a>
        <button type="button" class="ellamara-whatsapp-continue">Seguir explorando</button>
    </dialog>
    <?php
});
