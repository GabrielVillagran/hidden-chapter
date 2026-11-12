<?php
/** Catalog recovery and presentation. Does not change product contents or prices. */
declare(strict_types=1);
namespace Ellamara\Catalog;
defined('ABSPATH') || exit;

function empty_catalog(): string {
    $html='<section class="ellamara-empty-catalog" aria-label="Opciones para seguir buscando"><h2>No encontramos productos con esta selección</h2><p>Prueba otro título, autor o ISBN, o explora los productos disponibles.</p>';
    $id=wp_unique_id('ellamara-search-again-');
    $html.='<form role="search" method="get" action="'.esc_url(home_url('/')).'"><label for="'.esc_attr($id).'">Buscar de nuevo</label><input id="'.esc_attr($id).'" type="search" name="s" placeholder="Título, autor o ISBN" value="'.esc_attr(get_search_query()).'"><input type="hidden" name="post_type" value="product"><button type="submit">Buscar</button></form>';
    $html.='<div class="ellamara-empty-actions"><a href="'.esc_url(available_catalog_url()).'">Ver disponibles</a><a href="'.esc_url(wc_get_page_permalink('shop')).'">Ver toda la tienda y limpiar filtros</a></div>';
    $candidates=wc_get_products(['status'=>'publish','visibility'=>'visible','stock_status'=>'instock','limit'=>3,'orderby'=>'title','order'=>'ASC']);
    $ids=array_map(static fn($p)=>$p->get_id(),array_filter($candidates,__NAMESPACE__.'\\alternative_is_eligible'));
    if($ids){$html.='<h3>Puedes empezar por estos disponibles</h3>'.recovery_cards($candidates);}
    return $html.'</section>';
}
/** Explicit cards also render when an empty block archive removes classic loop hooks. */
function recovery_cards(array $products): string {
    $html='<ul class="ellamara-recovery-cards">';
    foreach($products as $item){
        if(!alternative_is_eligible($item)){continue;}
        $url=get_permalink($item->get_id());
        $html.='<li><a href="'.esc_url($url).'">'.$item->get_image('woocommerce_thumbnail').'<h3>'.esc_html($item->get_name()).'</h3></a>'.card_presentation($item).'<div>'.$item->get_price_html().'</div><a class="ellamara-recovery-view" href="'.esc_url($url).'">Ver producto →</a></li>';
    }
    return $html.'</ul>';
}
add_filter('woocommerce_page_title',static fn(string $title):string=>is_search()?'Resultados de búsqueda':$title,40);
add_action('wp',static function():void {
    if(!is_listing()){return;}
    remove_action('woocommerce_no_products_found','wc_no_products_found',10);
    add_action('woocommerce_no_products_found',static function():void {echo empty_catalog();},10);
},20);

function card_presentation(\WC_Product $product): string {
    $format=trim((string)$product->get_meta('_ellamara_book_format'));
    $kit=has_term('kits-literarios','product_cat',$product->get_id());
    if(!$kit && $format===''){return '';}
    return '<span class="ellamara-card-presentation">'.esc_html(($kit?'Kits literarios':'Libro').($format!==''?' · '.$format:'')).'</span>';
}
add_action('woocommerce_shop_loop_item_title',static function():void {global $product;if($product instanceof \WC_Product){echo card_presentation($product);}},15);
add_filter('render_block_core/post-title',static function(string $html,array $attrs,\WP_Block $block):string {
    if(str_contains($html,'ellamara-card-presentation')){return $html;}
    $id=(int)($block->context['postId']??0);
    if(!$id || (is_product() && $id===get_queried_object_id())){return $html;}
    $product=wc_get_product($id);
    return $product ? $html.card_presentation($product) : $html;
},40,3);

function shares_interest(\WC_Product $seed,\WC_Product $candidate): bool {
    $author=trim((string)$seed->get_meta('_ellamara_book_author'));
    if($author!=='' && $author===$candidate->get_meta('_ellamara_book_author')){return true;}
    $a=wp_get_object_terms($seed->get_id(),TAXONOMY,['fields'=>'ids']);
    $b=wp_get_object_terms($candidate->get_id(),TAXONOMY,['fields'=>'ids']);
    return !is_wp_error($a) && !is_wp_error($b) && (bool)array_intersect($a,$b);
}
add_filter('render_block_woocommerce/product-collection',static function(string $html,array $block):string {
    if(!is_product() || ($block['attrs']['collection']??'')!=='woocommerce/product-collection/related'){return $html;}
    $seed=wc_get_product(get_queried_object_id());
    if(!$seed || is_coming_soon($seed)){return $html;}
    $pool=available_alternatives($seed,4);
    if(!$pool){return '';}
    $relevant=array_values(array_filter($pool,static fn($p)=>shares_interest($seed,$p)));
    $chosen=$relevant ?: $pool;
    $title=$relevant?'Productos relacionados':'Otros libros disponibles';
    $ids=array_map(static fn($p)=>$p->get_id(),$chosen);
    $cards=do_shortcode('[products ids="'.implode(',',$ids).'" columns="4" orderby="post__in"]');
    $tags=new \WP_HTML_Tag_Processor($cards);
    while($tags->next_tag('UL')){if($tags->has_class('products')){$tags->add_class('wc-block-product-template');break;}}
    return '<section class="ellamara-related" aria-label="'.esc_attr($title).'" aria-roledescription="carrusel"><div class="ellamara-carousel-controls" hidden><button type="button" data-carousel-prev aria-label="Ver productos relacionados anteriores">←</button><button type="button" data-carousel-next aria-label="Ver más productos relacionados">→</button></div><h2>'.esc_html($title).'</h2>'.$tags->get_updated_html().'<p class="ellamara-carousel-hint">Desliza para explorar más productos.</p></section>';
},35,2);
