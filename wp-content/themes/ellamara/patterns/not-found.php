<?php
/**
 * Title: Página no encontrada
 * Slug: ellamara/not-found
 * Categories: ellamara
 * Inserter: no
 */
?>
<!-- wp:group {"className":"ellamara-recovery ellamara-not-found","layout":{"type":"default"}} -->
<div class="wp-block-group ellamara-recovery ellamara-not-found">
<!-- wp:group {"className":"ellamara-recovery-grid","layout":{"type":"default"}} -->
<div class="wp-block-group ellamara-recovery-grid">
<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ellamara-recovery-art"} -->
<figure class="wp-block-image size-full ellamara-recovery-art"><img src="<?php echo esc_url(get_theme_file_uri('/assets/images/not-found.png')); ?>" alt="" width="630" height="396" /></figure>
<!-- /wp:image -->
<!-- wp:group {"className":"ellamara-recovery-copy","layout":{"type":"default"}} -->
<div class="wp-block-group ellamara-recovery-copy">
<!-- wp:paragraph {"className":"ellamara-recovery-eyebrow"} --><p class="ellamara-recovery-eyebrow">Un pequeño desvío · 404</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">Esta página se quedó entre capítulos</h1><!-- /wp:heading -->
<!-- wp:paragraph --><p>El enlace puede haber cambiado o la página ya no está disponible. Busca tu próxima historia o vuelve a explorar la tienda.</p><!-- /wp:paragraph -->
<!-- wp:search {"label":"Buscar en la tienda","showLabel":true,"placeholder":"Título, autor o ISBN","buttonText":"Buscar","query":{"post_type":"product"}} /-->
<!-- wp:buttons --><div class="wp-block-buttons">
<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Explorar la tienda</a></div><!-- /wp:button -->
</div><!-- /wp:buttons -->
</div><!-- /wp:group -->
</div><!-- /wp:group -->
</div><!-- /wp:group -->
