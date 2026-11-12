<?php
/** Visitor-controlled help. No modal or automatic focus changes. */
declare(strict_types=1);
namespace Ellamara\Theme;
defined('ABSPATH') || exit;
add_action('wp_enqueue_scripts',static function():void {
    $path='/assets/css/whatsapp-welcome.css';
    wp_enqueue_style('ellamara-whatsapp-welcome',get_theme_file_uri($path),['ellamara-experience'],(string)filemtime(get_theme_file_path($path)));
    if(function_exists('is_cart') && is_cart()){
        wp_enqueue_script('ellamara-whatsapp-cart',get_theme_file_uri('/assets/js/whatsapp-cart.js'),[],(string)filemtime(get_theme_file_path('/assets/js/whatsapp-cart.js')),true);
    }
});
add_action('wp_footer',static function():void {
    if(is_admin()){return;}
    if (function_exists('\\Ellamara\\Cart\\catalog_only') && \Ellamara\Cart\catalog_only()) {
        echo '<details class="ellamara-support"><summary>Compras próximamente</summary><div class="ellamara-support-panel"><strong>Compras próximamente</strong><p>' . esc_html(\Ellamara\Cart\CATALOG_MESSAGE) . '</p></div></details>';
        return;
    }
    echo '<details class="ellamara-support"><summary><span aria-hidden="true">?</span> Ayuda</summary><div class="ellamara-support-panel"><strong>¿Necesitas ayuda?</strong><p>Consulta productos, tu pedido o las opciones de pago.</p><a href="https://wa.me/message/3LIIIQSYBGX5G1" target="_blank" rel="noopener noreferrer">Escribir por WhatsApp ↗</a><a href="'.esc_url(home_url('/contacto/')).'">Enviar un mensaje</a></div></details>';
});
