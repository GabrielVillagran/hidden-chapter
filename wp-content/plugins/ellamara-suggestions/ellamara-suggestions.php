<?php
/**
 * Plugin Name: Ellamara · Sugerencias
 * Description: Afinidad de catálogo a partir de la actividad de la pestaña, sin servicios de analítica.
 * Version: 0.1.0
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 */
declare(strict_types=1);
namespace Ellamara\Suggestions;
defined('ABSPATH') || exit;

function eligible($product): bool {
    return $product instanceof \WC_Product && $product->get_status() === 'publish'
        && in_array($product->get_catalog_visibility(), ['visible', 'catalog'], true)
        && $product->is_in_stock() && $product->is_purchasable();
}

function terms(int $id, string $taxonomy): array {
    $terms = get_the_terms($id, $taxonomy);
    return !$terms || is_wp_error($terms) ? [] : array_map('intval', wp_list_pluck($terms, 'term_id'));
}

function categories(\WC_Product $product): array {
    $ids = $product->get_category_ids();
    foreach ($ids as $id) { $ids = array_merge($ids, get_ancestors($id, 'product_cat', 'taxonomy')); }
    return array_values(array_unique(array_map('intval', $ids)));
}

/** Bounded candidate pool and explicit affinity rules, rather than a claim of AI. */
function recommendations(array $views, string $search): array {
    $views = array_slice(array_values(array_unique(array_filter(array_map('absint', $views)))), 0, 8);
    $seeds = [];
    foreach (array_slice($views, 0, 3) as $id) {
        $product = wc_get_product($id);
        if (eligible($product)) { $seeds[] = $product; }
    }
    $pool = wc_get_products(['status' => 'publish', 'stock_status' => 'instock', 'limit' => 60, 'orderby' => 'date', 'order' => 'DESC']);
    $related_ids = [];
    $seed_genres = [];
    $seed_categories = [];
    foreach ($seeds as $seed) {
        $related_ids = array_merge($related_ids, wc_get_related_products($seed->get_id(), 12, $views));
        $seed_genres = array_merge($seed_genres, terms($seed->get_id(), 'ellamara_genre'));
        $seed_categories = array_merge($seed_categories, categories($seed));
    }
    foreach (array_unique($related_ids) as $id) { $pool[] = wc_get_product($id); }
    $pool_ids = [];
    foreach ($pool as $product) { if ($product instanceof \WC_Product) { $pool_ids[] = $product->get_id(); } }
    update_object_term_cache(array_unique($pool_ids), 'product');
    $search = mb_substr(sanitize_text_field($search), 0, 80);
    $words = preg_split('/\s+/u', mb_strtolower(remove_accents($search)), -1, PREG_SPLIT_NO_EMPTY);
    $words = array_slice(array_filter($words ?: [], static fn ($word) => mb_strlen($word) >= 2), 0, 8);
    $ranked = [];
    foreach ($pool as $product) {
        if (!eligible($product) || in_array($product->get_id(), $views, true)) { continue; }
        $id = $product->get_id();
        $genres = terms($id, 'ellamara_genre');
        $score = count(array_intersect($genres, $seed_genres)) * 45
            + count(array_intersect(categories($product), $seed_categories)) * 75
            + (in_array($id, $related_ids, true) ? 20 : 0);
        $text = mb_strtolower(remove_accents(wp_strip_all_tags($product->get_name() . ' ' . $product->get_short_description() . ' ' . $product->get_meta('_ellamara_book_author'))));
        $genre_terms = get_the_terms($id, 'ellamara_genre');
        if ($genre_terms && !is_wp_error($genre_terms)) { $text .= ' ' . mb_strtolower(remove_accents(implode(' ', wp_list_pluck($genre_terms, 'name')))); }
        foreach ($words as $word) { if (str_contains($text, $word)) { $score += 25; } }
        $ranked[$id] = ['id' => $id, 'score' => $score];
    }
    usort($ranked, static fn ($a, $b) => ($b['score'] <=> $a['score']) ?: ($b['id'] <=> $a['id']));
    $affinity = array_values(array_filter($ranked, static fn ($item) => $item['score'] > 0));
    $personalized = !empty($affinity);
    $items = array_slice($personalized ? $affinity : $ranked, 0, 8);
    $ids = array_column($items, 'id');
    $html = $ids ? do_shortcode('[products ids="' . implode(',', $ids) . '" columns="4" orderby="post__in"]') : '<p>No hay sugerencias disponibles por ahora. Explora las secciones de nuestra tienda.</p>';
    return ['html' => $html, 'personalized' => $personalized, 'ids' => $ids,
        'message' => $personalized ? 'Una selección afín a tus búsquedas y a los productos que consultaste en esta pestaña.' : 'Una selección del catálogo para empezar a descubrir. Visita productos o busca en la tienda para orientar tus sugerencias.'];
}

add_action('rest_api_init', static function (): void {
    register_rest_route('ellamara/v1', '/suggestions', [
        'methods' => 'POST', 'permission_callback' => '__return_true',
        'args' => [
            'views' => ['type' => 'array', 'items' => ['type' => 'integer'], 'maxItems' => 8, 'default' => []],
            'search' => ['type' => 'string', 'maxLength' => 80, 'default' => ''],
        ],
        'callback' => static function (\WP_REST_Request $request): \WP_REST_Response {
            // This public read-only endpoint returns published catalog data only.
            $response = new \WP_REST_Response(recommendations($request['views'], $request['search']));
            $response->header('Cache-Control', 'private, no-store');
            return $response;
        },
    ]);
});

add_shortcode('ellamara_suggestions', static function (): string {
    $selection = recommendations([], '');
    return '<section class="ellamara-suggestions" aria-label="Sugerencias de la tienda"><div class="ellamara-suggestions-intro"><div><h2>Tu próxima historia puede estar aquí</h2><p class="ellamara-suggestions-message" role="status">' . esc_html($selection['message']) . '</p></div><button type="button" class="ellamara-suggestions-reset" hidden>Restablecer sugerencias</button></div><p class="ellamara-suggestions-privacy">Sin servicios de analítica: usamos solo la actividad de esta pestaña durante la sesión. Puedes restablecerla cuando quieras.</p><div class="ellamara-suggestions-results">' . $selection['html'] . '</div></section>';
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_script('ellamara-suggestions', plugin_dir_url(__FILE__) . 'suggestions.js', [], (string) filemtime(__DIR__ . '/suggestions.js'), true);
    wp_localize_script('ellamara-suggestions', 'ellamaraSuggestions', [
        'endpoint' => rest_url('ellamara/v1/suggestions'),
        'product' => function_exists('is_product') && is_product() ? get_queried_object_id() : 0,
        'search' => is_search() && function_exists('is_shop') && is_shop() ? mb_substr(get_search_query(false), 0, 80) : '',
    ]);
    if (is_page('sugerencias')) { wp_enqueue_style('ellamara-suggestions', plugin_dir_url(__FILE__) . 'suggestions.css', ['ellamara-cards'], (string) filemtime(__DIR__ . '/suggestions.css')); }
});
