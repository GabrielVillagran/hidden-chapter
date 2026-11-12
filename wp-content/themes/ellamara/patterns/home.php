<?php
/**
 * Title: Inicio · Productos primero
 * Slug: ellamara/home
 * Categories: ellamara
 * Description: Catálogo como contenido principal, seguido de Sobre nosotros.
 */
defined('ABSPATH') || exit;
?>
<!-- wp:group {"className":"ellamara-catalog","layout":{"type":"constrained"}} -->
<div class="wp-block-group ellamara-catalog">
<!-- wp:heading {"level":1,"align":"wide","fontSize":"x-large"} --><h1 class="wp-block-heading alignwide has-x-large-font-size">Encuentra tu próximo tesoro</h1><!-- /wp:heading -->
<!-- wp:woocommerce/product-collection {"queryId":0,"query":{"perPage":8,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"title","inherit":false},"displayLayout":{"type":"flex","columns":4},"align":"wide"} -->
<div class="wp-block-woocommerce-product-collection alignwide">
<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":true,"isDescendentOfQueryLoop":true} /-->
<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"medium"} /-->
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->
<!-- /wp:woocommerce/product-template -->
<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous {"label":"Anterior"} /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next {"label":"Siguiente"} /-->
<!-- /wp:query-pagination -->
<!-- wp:woocommerce/product-collection-no-results -->
<!-- wp:paragraph --><p>Pronto encontrarás nuevos tesoros en esta sección.</p><!-- /wp:paragraph -->
<!-- /wp:woocommerce/product-collection-no-results -->
</div><!-- /wp:woocommerce/product-collection -->
</div><!-- /wp:group -->
<!-- wp:pattern {"slug":"ellamara/about"} /-->
