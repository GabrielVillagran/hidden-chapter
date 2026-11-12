<?php
/**
 * Plugin Name: Ellamara · Catálogo por género
 * Description: Clasificación literaria y filtros por género para los listados de WooCommerce.
 * Version: 0.9.0
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 */
declare(strict_types=1);
namespace Ellamara\Catalog;
defined('ABSPATH') || exit;
require_once __DIR__ . '/availability.php';

const TAXONOMY = 'ellamara_genre';
const PARAMETER = 'genero';
const AVAILABILITY_PARAMETER = 'disponibilidad';
require_once __DIR__ . '/discovery.php';
require_once __DIR__ . '/alternatives.php';
require_once __DIR__ . '/ux.php';

function register_genres(): void {
    register_taxonomy(TAXONOMY, ['product'], [
        'labels' => ['name' => 'Géneros literarios', 'singular_name' => 'Género literario', 'menu_name' => 'Géneros literarios', 'add_new_item' => 'Añadir género literario'],
        'public' => false, 'show_ui' => true, 'show_admin_column' => true,
        'show_in_rest' => true, 'hierarchical' => true, 'rewrite' => false,
        'capabilities' => ['manage_terms' => 'manage_product_terms', 'edit_terms' => 'edit_product_terms', 'delete_terms' => 'delete_product_terms', 'assign_terms' => 'assign_product_terms'],
    ]);
}
add_action('init', __NAMESPACE__ . '\\register_genres');

// One-time migration copies only explicit genre tags; it never guesses from titles.
register_activation_hook(__FILE__, static function (): void {
    register_genres();
    if (get_option('ellamara_genres_initialized')) { return; }
    foreach (['fantasia' => 'Fantasía', 'romantasy' => 'Romantasy', 'romance' => 'Romance', 'juvenil' => 'Literatura juvenil', 'misterio' => 'Misterio', 'thriller' => 'Thriller', 'gotico' => 'Gótico'] as $slug => $name) {
        $term = term_exists($slug, TAXONOMY);
        if (!$term) { $term = wp_insert_term($name, TAXONOMY, ['slug' => $slug]); }
        if (is_wp_error($term)) { continue; }
        $tag = get_term_by('slug', $slug, 'product_tag');
        if (!$tag) { continue; }
        $products = get_objects_in_term($tag->term_id, 'product_tag');
        if (is_wp_error($products)) { continue; }
        foreach ($products as $product_id) { wp_set_object_terms((int) $product_id, [(int) $term['term_id']], TAXONOMY, true); }
    }
    update_option('ellamara_genres_initialized', 1, false);
});

function is_listing(): bool {
    return !is_admin() && !wp_doing_ajax() && !defined('REST_REQUEST') && (is_front_page() || is_shop() || is_product_taxonomy());
}

function selected_genre(): string {
    $value = $_GET[PARAMETER] ?? '';
    $slug = is_string($value) ? sanitize_title(wp_unslash($value)) : '';
    $aliases = get_option('ellamara_genre_aliases', []);
    return $aliases[$slug] ?? $slug;
}

function filter_query(array $args): array {
    $slug = selected_genre();
    // Nested AND preserves category, visibility, stock and any existing OR clauses.
    if ($slug !== '') {
        $clause = ['taxonomy' => TAXONOMY, 'field' => 'slug', 'terms' => [$slug]];
        $args['tax_query'] = empty($args['tax_query']) ? [$clause] : ['relation' => 'AND', $args['tax_query'], $clause];
    }
    $availability = selected_availability();
    if ($availability !== '') {
        $clause = ['relation' => 'AND', ['key' => '_stock_status', 'value' => $availability === 'disponibles' ? 'instock' : 'outofstock']];
        if ($availability === 'proximamente') { $clause[] = ['key' => '_ellamara_availability', 'value' => 'proximamente']; }
        $args['meta_query'] = empty($args['meta_query']) ? $clause : ['relation' => 'AND', $args['meta_query'], $clause];
    }
    return $args;
}

function selected_availability(): string {
    $value = $_GET[AVAILABILITY_PARAMETER] ?? '';
    return is_string($value) && in_array($value, ['disponibles', 'proximamente'], true) ? $value : '';
}

add_filter('query_loop_block_query_vars', static function (array $args, \WP_Block $block): array {
    $is_product_query = ($args['post_type'] ?? '') === 'product' || ($block->context['query']['postType'] ?? '') === 'product';
    if (!is_listing() || !$is_product_query) { return $args; }
    $args = filter_query($args);
    $args['ellamara_catalog_listing'] = true;
    $args['ellamara_home_alphabetical'] = is_front_page();
    return $args;
}, 100, 2);
// Read the total from the real collection query, without running a second query.
add_filter('the_posts', static function (array $posts, \WP_Query $query): array {
    if ($query->get('ellamara_catalog_listing')) {
        $GLOBALS['ellamara_catalog_result_count'] = (int) $query->found_posts;
    }
    return $posts;
}, 100, 2);
add_action('woocommerce_product_query', static function (\WP_Query $query): void {
    if (!is_listing() || !$query->is_main_query()) { return; }
    $args = filter_query(['tax_query' => $query->get('tax_query'), 'meta_query' => $query->get('meta_query')]);
    $query->set('tax_query', $args['tax_query']);
    $query->set('meta_query', $args['meta_query']);
    $query->set('ellamara_catalog_listing', true);
    $query->set('ellamara_home_alphabetical', is_front_page());
});

function filter_url(string $slug, ?string $availability = null): string {
    $url = is_front_page() ? home_url('/') : (is_shop() ? wc_get_page_permalink('shop') : get_term_link(get_queried_object()));
    if (is_wp_error($url)) { $url = wc_get_page_permalink('shop'); }
    // Keep search and other filters, but reset pagination and the removed sorting.
    foreach ($_GET as $key => $value) {
        if (!is_string($value) || in_array($key, [PARAMETER, AVAILABILITY_PARAMETER, 'orderby', 'paged', 'page'], true) || preg_match('/^query-.*-page$/', (string) $key)) { continue; }
        $url = add_query_arg(sanitize_key($key), sanitize_text_field(wp_unslash($value)), $url);
    }
    $availability = $availability ?? selected_availability();
    if (in_array($availability, ['disponibles', 'proximamente'], true)) { $url = add_query_arg(AVAILABILITY_PARAMETER, $availability, $url); }
    return $slug === '' ? $url : add_query_arg(PARAMETER, $slug, $url);
}

function catalog_filter_form(array $terms, string $selected): string {
    $url = filter_url('');
    $action = explode('?', $url, 2)[0];
    parse_str((string) wp_parse_url($url, PHP_URL_QUERY), $parameters);
    $id = wp_unique_id('ellamara-genre-');
    $html = '<form class="ellamara-catalog-filters" method="get" action="' . esc_url($action) . '" aria-label="Filtrar el catálogo">';
    foreach ($parameters as $key => $value) {
        if (is_string($value) && $key !== AVAILABILITY_PARAMETER) { $html .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">'; }
    }
    $html .= '<div class="ellamara-filter-field"><label for="' . esc_attr($id) . '">Género literario</label><select id="' . esc_attr($id) . '" name="' . PARAMETER . '"><option value="">Todos los géneros</option>';
    foreach ($terms as $term) {
        $html .= '<option value="' . esc_attr($term->slug) . '"' . selected($selected, $term->slug, false) . '>' . esc_html($term->name) . '</option>';
    }
    $html .= '</select></div><div class="ellamara-filter-field"><label for="' . esc_attr($id) . '-availability">Disponibilidad</label><select id="' . esc_attr($id) . '-availability" name="' . AVAILABILITY_PARAMETER . '">';
    foreach (['' => 'Cualquier disponibilidad', 'disponibles' => 'Disponibles', 'proximamente' => 'Próximamente'] as $value => $label) {
        $html .= '<option value="' . esc_attr($value) . '"' . selected(selected_availability(), $value, false) . '>' . esc_html($label) . '</option>';
    }
    $html .= '</select></div><button type="submit">Aplicar filtros</button></form>';
    $active = (int)($selected !== '') + (int)(selected_availability() !== '');
    return '<details class="ellamara-filter-disclosure" open><summary>Filtrar' . ($active ? ' <span class="ellamara-filter-count">' . $active . ' ' . ($active === 1 ? 'activo' : 'activos') . '</span>' : '') . '<span aria-hidden="true">⌄</span></summary>' . $html . '</details>';
}

/** Contextual introductions for the three primary catalog destinations. */
function catalog_introduction(): string {
    if (is_front_page()) { return ''; }
    $copy = null;
    if (is_shop() && !is_search()) {
        $copy = ['01 · CATÁLOGO COMPLETO', 'Tienda', 'Explora todos nuestros tesoros y encuentra la historia con la que comenzará tu próxima aventura.'];
    } elseif (is_product_category('libros')) {
        $copy = ['02 · LIBROS', 'Libros', 'Descubre nuestro catálogo de libros y déjate enamorar por tu próxima historia favorita.'];
    } elseif (is_product_category('marcapaginas')) {
        $copy = ['ACCESORIOS DE LECTURA', 'Marcapáginas', 'Encuentra un diseño para acompañar tu próxima lectura.'];
    } elseif (is_product_category('pines')) {
        $copy = ['ACCESORIOS', 'Pines', 'Explora los diseños de nuestra colección de pines.'];
    }
    if (!$copy) { return ''; }
    return '<header class="ellamara-catalog-introduction alignwide"><p class="ellamara-catalog-eyebrow">' . esc_html($copy[0]) . '</p><h1>' . esc_html($copy[1]) . '</h1><p class="ellamara-catalog-description">' . esc_html($copy[2]) . '</p></header>';
}
// The contextual introduction supplies the single page title and description.
add_filter('render_block_core/query-title', static fn(string $html): string => is_listing() && catalog_introduction() !== '' ? '' : $html);
add_filter('render_block_core/term-description', static fn(string $html): string => is_listing() && catalog_introduction() !== '' ? '' : $html);
add_filter('woocommerce_show_page_title', static fn(bool $show): bool => catalog_introduction() !== '' ? false : $show);

function pills(): string {
    $terms = get_terms(['taxonomy' => TAXONOMY, 'hide_empty' => true, 'orderby' => 'name']);
    if (is_wp_error($terms)) { $terms = []; }
    $selected = selected_genre();
    $html = catalog_filter_form($terms, $selected);
    global $wp_query;
    $count = is_front_page() ? ($GLOBALS['ellamara_catalog_result_count'] ?? null) : (int) $wp_query->found_posts;
    $status = '<div class="ellamara-browse-status">';
    if ($count !== null) { $status .= '<p role="status">' . esc_html(sprintf(_n('%s producto', '%s productos', $count, 'ellamara'), number_format_i18n($count))) . '</p>'; }
    if ($selected !== '' || selected_availability() !== '' || is_search()) {
        $clear_url = remove_query_arg(['s', 'post_type'], filter_url('', ''));
        $status .= '<a class="ellamara-clear-filters" href="' . esc_url($clear_url) . '">Limpiar filtros <span aria-hidden="true">×</span></a>';
    }
    $suggestions = get_page_by_path('sugerencias');
    if ($suggestions && $suggestions->post_status === 'publish') {
        $status .= '<a class="ellamara-suggestions-link" href="' . esc_url(get_permalink($suggestions)) . '">Artículos sugeridos <span aria-hidden="true">→</span></a>';
    }
    $intro = is_front_page() ? '<p class="ellamara-catalog-subtitle alignwide">Descubre libros y accesorios para acompañarte en cada nueva aventura.</p>' : '';
    return catalog_introduction() . $intro . '<section class="ellamara-browse alignwide" aria-label="Explorar el catálogo">' . category_navigation() . $html . $status . '</div></section>';
}

/** Category switches are separate from genre filters and clear the prior search. */
function category_navigation(): string {
    $categories = get_terms(['taxonomy' => 'product_cat', 'slug' => ['libros', 'marcapaginas', 'pines'], 'hide_empty' => true]);
    if (is_wp_error($categories)) { return ''; }
    if (count($categories) <= 1) { return ''; }
    $order = ['libros' => 0, 'marcapaginas' => 1, 'pines' => 2];
    usort($categories, static fn ($a, $b) => ($order[$a->slug] ?? 9) <=> ($order[$b->slug] ?? 9));
    $current = is_product_category() ? (int) get_queried_object_id() : 0;
    $ancestors = $current ? get_ancestors($current, 'product_cat', 'taxonomy') : [];
    $links = [['label' => 'Toda la tienda', 'url' => wc_get_page_permalink('shop'), 'active' => !$current && !is_search()]];
    foreach ($categories as $category) {
        $url = get_term_link($category);
        if (is_wp_error($url)) { continue; }
        $links[] = ['label' => $category->name, 'url' => $url, 'active' => $current === $category->term_id || in_array($category->term_id, $ancestors, true)];
    }
    $html = '<nav class="ellamara-categories" aria-label="Secciones de la tienda">';
    foreach ($links as $link) {
        $html .= '<a href="' . esc_url($link['url']) . '"' . ($link['active'] ? ' aria-current="page"' : '') . '>' . esc_html($link['label']) . '</a>';
    }
    return $html . '</nav>';
}

function listing_pills(): string {
    static $rendered = false;
    if ($rendered) { return ''; }
    $rendered = true;
    return pills();
}

add_action('wp', static function (): void {
    if (!is_listing()) { return; }
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    add_action('woocommerce_before_shop_loop', static function (): void { echo listing_pills(); }, 5);
    // Keep filters available when a category has no matching products.
    add_action('woocommerce_no_products_found', static function (): void { echo listing_pills(); }, 5);
});
add_filter('render_block_woocommerce/catalog-sorting', static fn (string $html): string => is_listing() ? '' : $html);
add_filter('render_block_woocommerce/product-results-count', static fn (string $html): string => is_listing() ? '' : $html);
add_filter('render_block_woocommerce/product-collection', static function (string $html): string {
    return is_listing() ? listing_pills() . $html : $html;
});
add_filter('render_block_woocommerce/product-collection-no-results', static function (string $html): string {
    return is_listing() && trim($html) !== '' ? empty_catalog() : $html;
});
add_action('wp_enqueue_scripts', static function (): void {
    if (is_listing()) {
        $deps = wp_style_is('ellamara-experience', 'enqueued') ? ['ellamara-experience'] : [];
        wp_enqueue_style('ellamara-genres', plugin_dir_url(__FILE__) . 'genres.css', $deps, (string) filemtime(__DIR__ . '/genres.css'));
        wp_enqueue_script('ellamara-catalog-ui', plugin_dir_url(__FILE__) . 'catalog-ui.js', [], (string) filemtime(__DIR__ . '/catalog-ui.js'), true);
    }
}, 20);
