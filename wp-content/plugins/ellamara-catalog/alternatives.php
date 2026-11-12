<?php
/** Recovery paths for incoming products. Never makes incoming stock purchasable. */
declare(strict_types=1);
namespace Ellamara\Catalog;
defined('ABSPATH') || exit;

function alternative_is_eligible($product): bool {
    return $product instanceof \WC_Product && $product->get_status() === 'publish'
        && in_array($product->get_catalog_visibility(), ['visible', 'catalog'], true)
        && $product->get_stock_status() === 'instock' && $product->is_purchasable();
}

/** A few bounded queries prefer the same author/genre, then category, then the catalog. */
function available_alternatives(\WC_Product $seed, int $limit = 3): array {
    $limit = max(1, min(4, $limit));
    $visibility = wc_get_product_visibility_term_ids();
    $base = ['post_type'=>'product', 'post_status'=>'publish', 'fields'=>'ids', 'posts_per_page'=>12, 'no_found_rows'=>true,
        'post__not_in'=>[$seed->get_id()], 'orderby'=>['date'=>'DESC', 'ID'=>'DESC'],
        'meta_query'=>[['key'=>'_stock_status', 'value'=>'instock']],
        'tax_query'=>[['taxonomy'=>'product_visibility', 'field'=>'term_taxonomy_id', 'terms'=>[$visibility['exclude-from-catalog']], 'operator'=>'NOT IN']]];
    $author = trim((string) $seed->get_meta('_ellamara_book_author'));
    $genres = wp_get_object_terms($seed->get_id(), TAXONOMY, ['fields'=>'ids']);
    $genres = is_wp_error($genres) ? [] : array_map('intval', $genres);
    $categories = array_map('intval', $seed->get_category_ids());
    $pool = [];
    if ($author !== '') {
        $args = $base; $args['meta_query'][] = ['key'=>'_ellamara_book_author', 'value'=>$author];
        $pool = get_posts($args);
    }
    foreach ([TAXONOMY=>$genres, 'product_cat'=>$categories] as $taxonomy=>$ids) {
        if (!$ids) { continue; }
        $args=$base; $args['tax_query'][]=['taxonomy'=>$taxonomy, 'field'=>'term_id', 'terms'=>$ids];
        $pool=array_merge($pool,get_posts($args));
    }
    if (count(array_unique($pool))<$limit) { $pool=array_merge($pool,get_posts($base)); }
    $pool=array_values(array_unique(array_map('intval',$pool)));
    update_object_term_cache($pool,'product');
    $ranked=[];
    foreach ($pool as $id) {
        $candidate=wc_get_product($id);
        if (!alternative_is_eligible($candidate) || $id===$seed->get_id()) { continue; }
        $terms=wp_get_object_terms($id,TAXONOMY,['fields'=>'ids']);
        $terms=is_wp_error($terms)?[]:array_map('intval',$terms);
        $score=($author!=='' && $candidate->get_meta('_ellamara_book_author')===$author ? 100 : 0)
            + count(array_intersect($genres,$terms))*40 + count(array_intersect($categories,$candidate->get_category_ids()))*5;
        $ranked[]=['product'=>$candidate,'score'=>$score];
    }
    usort($ranked,static fn($a,$b)=>($b['score']<=>$a['score']) ?: ($b['product']->get_id()<=>$a['product']->get_id()));
    return array_column(array_slice($ranked,0,$limit),'product');
}

function available_catalog_url(): string {
    return add_query_arg(AVAILABILITY_PARAMETER,'disponibles',wc_get_page_permalink('shop'));
}

function upcoming_recovery(\WC_Product $product): string {
    if (!is_coming_soon($product)) { return ''; }
    return '<aside class="ellamara-upcoming-recovery" aria-label="Opciones mientras llega este producto">'
        . '<p>Mientras llega, descubre lo que ya puedes comprar.</p>'
        . '<a class="ellamara-available-button" href="'.esc_url(available_catalog_url()).'">Explorar disponibles <span aria-hidden="true">→</span></a></aside>';
}

add_filter('render_block_woocommerce/add-to-cart-form',static function(string $html,array $attributes,\WP_Block $block): string {
    if (!is_product()) { return $html; }
    $id=(int)($block->context['postId']??get_queried_object_id());
    if ($id!==get_queried_object_id()) { return $html; }
    $product=wc_get_product($id);
    return $product ? $html.upcoming_recovery($product) : $html;
},40,3);

add_filter('render_block_woocommerce/product-collection',static function(string $html,array $block): string {
    if (!is_product() || ($block['attrs']['collection']??'')!=='woocommerce/product-collection/related') { return $html; }
    $product=wc_get_product(get_queried_object_id());
    if (!$product || !is_coming_soon($product)) { return $html; }
    $alternatives=available_alternatives($product);
    if (!$alternatives) {
        return '<section class="ellamara-available-alternatives"><h2>Sigue explorando Ellamara</h2><p>Por ahora no hay otras opciones disponibles para comprar.</p><a href="'.esc_url(wc_get_page_permalink('shop')).'">Ver el catálogo completo →</a></section>';
    }
    $ids=array_map(static fn($p)=>$p->get_id(),$alternatives);
    return '<section class="ellamara-available-alternatives" aria-label="Alternativas disponibles"><div class="ellamara-alternatives-heading"><div><h2>Disponibles para descubrir</h2><p>Estas opciones sí están disponibles para comprar ahora.</p></div><a href="'.esc_url(available_catalog_url()).'">Ver todos los disponibles <span aria-hidden="true">→</span></a></div>'
        .do_shortcode('[products ids="'.implode(',',$ids).'" columns="3" orderby="post__in"]').'</section>';
},40,2);

add_action('wp_enqueue_scripts',static function(): void {
    if (!is_product()) { return; }
    $product=wc_get_product(get_queried_object_id());
    if ($product && is_coming_soon($product)) {
        wp_enqueue_style('ellamara-alternatives',plugin_dir_url(__FILE__).'alternatives.css',[],(string)filemtime(__DIR__.'/alternatives.css'));
    }
},30);
