<?php
/**
 * Title: Pie de tienda Ellamara
 * Slug: ellamara/footer-store
 * Categories: ellamara
 * Inserter: no
 */
defined('ABSPATH') || exit;

$shop = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
$contact = get_page_by_path('contacto');
$groups = [
    'Conócenos' => [['label' => 'Sobre nosotros', 'url' => home_url('/#sobre-nosotros')], ['label' => 'Explora la tienda', 'url' => $shop]],
    'Te ayudamos' => [],
    'Políticas' => [],
];
if (function_exists('wc_get_page_id') && !(function_exists('\\Ellamara\\Cart\\catalog_only') && \Ellamara\Cart\catalog_only())) {
    foreach (['myaccount' => 'Mi cuenta y pedidos', 'cart' => 'Mi carrito'] as $key => $label) {
        $id = wc_get_page_id($key);
        if ($id > 0 && get_post_status($id) === 'publish') {
            $groups['Te ayudamos'][] = ['label' => $label, 'url' => get_permalink($id)];
        }
    }
}
foreach ([
    ['preguntas-frecuentes', 'Preguntas frecuentes', 'Te ayudamos'],
    ['envios', 'Envíos y entregas', 'Te ayudamos'],
    ['terminos-y-condiciones', 'Términos y condiciones', 'Políticas'],
    ['devoluciones', 'Política de devolución', 'Políticas'],
    ['cambios-y-garantias', 'Cambios y garantías', 'Políticas'],
] as [$slug, $label, $group]) {
    $page = get_page_by_path($slug);
    if ($page && $page->post_status === 'publish') {
        $groups[$group][] = ['label' => $label, 'url' => get_permalink($page)];
    }
}
$privacy_id = (int) get_option('wp_page_for_privacy_policy');
if ($privacy_id > 0 && get_post_status($privacy_id) === 'publish') {
    $groups['Políticas'][] = ['label' => 'Privacidad y datos personales', 'url' => get_permalink($privacy_id)];
}
?>
<!-- wp:group {"align":"full","className":"ellamara-footer ellamara-store-footer","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull ellamara-footer ellamara-store-footer">
<!-- wp:group {"align":"wide","className":"ellamara-footer-grid","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide ellamara-footer-grid">
<!-- wp:group {"className":"ellamara-footer-identity","layout":{"type":"default"}} -->
<div class="wp-block-group ellamara-footer-identity">
<!-- wp:site-logo {"width":88,"shouldSyncIcon":false} /-->
<!-- wp:site-title {"level":0} /-->
<!-- wp:paragraph --><p>Libros y accesorios para lectores.</p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
<!-- wp:group {"className":"ellamara-footer-column ellamara-footer-contact","layout":{"type":"default"}} -->
<div class="wp-block-group ellamara-footer-column ellamara-footer-contact">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Contáctanos</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><a href="mailto:ellamarahiddenchapter@gmail.com">ellamarahiddenchapter@gmail.com</a></p><!-- /wp:paragraph -->
<?php if ($contact && $contact->post_status === 'publish') : ?>
<!-- wp:paragraph --><p><a href="<?php echo esc_url(get_permalink($contact)); ?>">Envíanos un mensaje</a></p><!-- /wp:paragraph -->
<?php endif; ?>
<!-- wp:paragraph --><p>Teléfono: <a href="tel:+573193224675">+57 319 322 4675</a></p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Horario: Lunes a Viernes, 9:00 a. m. – 10:00 p. m.</p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
<?php foreach ($groups as $title => $links) : if (!$links) { continue; } ?>
<!-- wp:group {"className":"ellamara-footer-column","layout":{"type":"default"}} -->
<div class="wp-block-group ellamara-footer-column">
<!-- wp:heading {"level":2} --><h2 class="wp-block-heading"><?php echo esc_html($title); ?></h2><!-- /wp:heading -->
<!-- wp:navigation {"overlayMenu":"never","ariaLabel":<?php echo wp_json_encode('Pie de página: ' . $title); ?>,"layout":{"type":"flex","orientation":"vertical"},"metadata":{"ignoredHookedBlocks":["woocommerce/customer-account"]}} -->
<?php foreach ($links as $link) : ?>
<!-- wp:navigation-link <?php echo wp_json_encode($link, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?> /-->
<?php endforeach; ?>
<!-- /wp:navigation -->
</div><!-- /wp:group -->
<?php endforeach; ?>
</div><!-- /wp:group -->
<!-- wp:group {"align":"wide","className":"ellamara-footer-bottom","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide ellamara-footer-bottom">
<!-- wp:paragraph --><p>© <?php echo esc_html(wp_date('Y') . ' ' . get_bloginfo('name')); ?></p><!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"ellamara-footer-legal"} --><p class="ellamara-footer-legal"><a href="https://www.sic.gov.co/">Protección al consumidor · SIC</a></p><!-- /wp:paragraph -->
</div><!-- /wp:group -->
</div><!-- /wp:group -->
