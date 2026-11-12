<?php
/**
 * Title: Menú de la tienda
 * Slug: ellamara/header-menu
 * Categories: ellamara
 * Inserter: no
 * Description: Enlaces nativos editables a tienda, categorías y páginas publicadas.
 */
defined('ABSPATH') || exit;

$links = [];
if (function_exists('wc_get_page_id')) {
    $shop_id = wc_get_page_id('shop');
    if ($shop_id > 0 && get_post_status($shop_id) === 'publish') {
        $links[] = ['label' => __('Tienda', 'ellamara'), 'url' => get_permalink($shop_id)];
    }
}

// Only link to categories that actually exist; never invent catalog destinations.
$categories = get_terms([
    'taxonomy' => 'product_cat',
    'slug' => ['libros', 'marcapaginas', 'pines', 'merch'],
    'hide_empty' => true,
    'orderby' => 'name',
]);
if (!is_wp_error($categories)) {
    foreach ($categories as $category) {
        $url = get_term_link($category);
        if (!is_wp_error($url)) {
            $links[] = ['label' => $category->name, 'url' => $url];
        }
    }
}

$about_page_found = false;
foreach (['sugerencias', 'sagas', 'sobre-nosotros', 'acerca-de', 'preguntas-frecuentes', 'contacto'] as $slug) {
    $page = get_page_by_path($slug);
    if ($page && $page->post_status === 'publish') {
        $links[] = ['label' => get_the_title($page), 'url' => get_permalink($page)];
        $about_page_found = $about_page_found || in_array($slug, ['sobre-nosotros', 'acerca-de'], true);
    }
}

if (!$about_page_found) {
    $links[] = [
        'label' => __('Sobre nosotros', 'ellamara'),
        'url' => is_front_page() ? '#sobre-nosotros' : home_url('/#sobre-nosotros'),
    ];
}

if (!$links) {
    $links[] = ['label' => __('Inicio', 'ellamara'), 'url' => home_url('/')];
}
?>
<!-- wp:navigation {"className":"ellamara-menu","textColor":"gold","overlayMenu":"always","ariaLabel":"Navegación principal","metadata":{"ignoredHookedBlocks":["woocommerce/customer-account"]},"layout":{"type":"flex","justifyContent":"right"}} -->
<?php foreach ($links as $link) : ?>
<!-- wp:navigation-link <?php echo wp_json_encode($link, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?> /-->
<?php endforeach; ?>
<!-- /wp:navigation -->
