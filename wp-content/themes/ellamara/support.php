<?php
/** Help panel and temporary storefront announcement. */
declare(strict_types=1);
namespace Ellamara\Theme;
defined('ABSPATH') || exit;

function show_catalog_announcement(): bool {
    return function_exists('\\Ellamara\\Cart\\catalog_only') && \Ellamara\Cart\catalog_only();
}

add_action('wp_enqueue_scripts',static function():void {
    $path='/assets/css/whatsapp-welcome.css';
    wp_enqueue_style('ellamara-whatsapp-welcome',get_theme_file_uri($path),['ellamara-experience'],(string)filemtime(get_theme_file_path($path)));
    if (show_catalog_announcement()) {
        $script = '/assets/js/catalog-announcement.js';
        wp_enqueue_script('ellamara-catalog-announcement', get_theme_file_uri($script), [], (string) filemtime(get_theme_file_path($script)), true);
    }
    if(function_exists('is_cart') && is_cart()){
        wp_enqueue_script('ellamara-whatsapp-cart',get_theme_file_uri('/assets/js/whatsapp-cart.js'),[],(string)filemtime(get_theme_file_path('/assets/js/whatsapp-cart.js')),true);
    }
});
add_action('wp_footer',static function():void {
    if(is_admin()){return;}
    $catalog_only = show_catalog_announcement();
    $help_copy = $catalog_only ? '¿Tienes preguntas sobre nuestros libros o la tienda?' : 'Consulta productos, tu pedido o las opciones de pago.';
    echo '<details class="ellamara-support"><summary><span aria-hidden="true">?</span> Ayuda</summary><div class="ellamara-support-panel"><strong>¿Necesitas ayuda?</strong><p>' . esc_html($help_copy) . '</p><a href="https://wa.me/message/3LIIIQSYBGX5G1" target="_blank" rel="noopener noreferrer">Escribir por WhatsApp ↗</a><a href="'.esc_url(home_url('/contacto/')).'">Enviar un mensaje</a></div></details>';
    if ($catalog_only) {
        echo '<dialog id="ellamara-catalog-announcement" class="ellamara-whatsapp-welcome" aria-labelledby="ellamara-announcement-title" aria-describedby="ellamara-announcement-copy">'
            . '<h2 id="ellamara-announcement-title">Compras en línea, próximamente</h2>'
            . '<p id="ellamara-announcement-copy">Ya puedes explorar nuestro catálogo. Estamos preparando la tienda para que pronto puedas hacer tu pedido en línea.</p>'
            . '<form method="dialog"><button type="submit" class="ellamara-whatsapp-link" autofocus>Explorar el catálogo</button></form></dialog>';
    }
});
