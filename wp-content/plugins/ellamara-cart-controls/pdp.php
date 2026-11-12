<?php
/** Product facts belong to products, so they survive a theme change. */
namespace Ellamara\Product;
defined('ABSPATH') || exit;

function fields(): array {
    return ['author' => 'Autor/a', 'isbn' => 'ISBN-13', 'edition' => 'Edición', 'publisher' => 'Editorial', 'date' => 'Fecha de publicación', 'language' => 'Idioma', 'format' => 'Encuadernación'];
}

function is_book(\WC_Product $product): bool {
    $root = get_term_by('slug', 'libros', 'product_cat');
    if (!$root) { return false; }
    foreach ($product->get_category_ids() as $id) {
        if ($id === $root->term_id || in_array($root->term_id, get_ancestors($id, 'product_cat', 'taxonomy'), true)) { return true; }
    }
    return false;
}

add_action('woocommerce_product_options_general_product_data', static function (): void {
    echo '<div class="options_group"><h4 style="padding-left:12px">Ellamara · Detalles y opiniones editoriales</h4>';
    foreach (fields() as $key => $label) {
        $input = ['id' => '_ellamara_book_' . $key, 'label' => $label, 'type' => 'text'];
        if ($key === 'date') {
            $input['placeholder'] = 'AAAA-MM-DD';
            $input['description'] = 'Admite AAAA, AAAA-MM o AAAA-MM-DD. Completa solo la precisión confirmada para este ISBN.';
        }
        woocommerce_wp_text_input($input);
    }
    woocommerce_wp_textarea_input(['id' => '_ellamara_editorial_review', 'label' => 'Opiniones editoriales', 'description' => 'Por bloque: crítica, salto de línea y autor o medio. Separa las críticas con una línea vacía. La sinopsis va en la descripción principal.']);
    woocommerce_wp_text_input(['id' => '_ellamara_review_source', 'label' => 'Autor o medio de la crítica']);
    woocommerce_wp_text_input(['id' => '_ellamara_review_url', 'label' => 'Enlace a la fuente', 'type' => 'url']);
    woocommerce_wp_text_input(['id' => '_ellamara_synopsis_source', 'label' => 'Fuente de la sinopsis']);
    woocommerce_wp_text_input(['id' => '_ellamara_synopsis_url', 'label' => 'Enlace de la sinopsis', 'type' => 'url']);
    woocommerce_wp_textarea_input(['id' => '_ellamara_author_bio', 'label' => 'Biografía del autor', 'description' => 'Presentación del autor. Usa párrafos separados por una línea vacía.']);
    woocommerce_wp_text_input(['id' => '_ellamara_author_bio_source', 'label' => 'Fuente de la biografía']);
    woocommerce_wp_text_input(['id' => '_ellamara_author_bio_url', 'label' => 'Enlace de la biografía', 'type' => 'url']);
    echo '</div>';
});
add_action('woocommerce_admin_process_product_object', static function (\WC_Product $product): void {
    // WooCommerce already checks the product editor's capability and nonce.
    foreach (array_merge(array_map(static fn ($key) => 'book_' . $key, array_keys(fields())), ['editorial_review', 'review_source', 'review_url', 'synopsis_source', 'synopsis_url', 'author_bio', 'author_bio_source', 'author_bio_url']) as $field) {
        $key = '_ellamara_' . $field;
        if (!isset($_POST[$key]) || !is_string($_POST[$key])) { continue; }
        $value = wp_unslash($_POST[$key]);
        $value = in_array($field, ['review_url', 'synopsis_url', 'author_bio_url'], true) ? esc_url_raw($value) : (in_array($field, ['editorial_review', 'author_bio'], true) ? sanitize_textarea_field($value) : sanitize_text_field($value));
        $product->update_meta_data($key, $value);
    }
});

function remaining(\WC_Product $product): int {
    $maximum = $product->get_max_purchase_quantity();
    $limit = $maximum < 0 ? 10 : min(10, (int) $maximum);
    $quantities = WC()->cart ? WC()->cart->get_cart_item_quantities() : [];
    $in_cart = (int) ($quantities[$product->get_stock_managed_by_id()] ?? 0);
    if ($product->managing_stock()) { $limit = min($limit, (int) $product->get_stock_quantity() - $in_cart); }
    if ($product->is_sold_individually()) { $limit = $in_cart ? 0 : 1; }
    return max(0, $limit);
}

add_filter('wc_get_template', static function (string $template, string $name): string {
    global $product;
    if ($name === 'global/quantity-input.php' && is_product() && $product instanceof \WC_Product && $product->is_type('simple')) {
        return __DIR__ . '/quantity-select.php';
    }
    return $template;
}, 20, 2);

add_filter('woocommerce_add_to_cart_validation', static function (bool $valid, int $id, int $quantity): bool {
    if (empty($_POST['ellamara_pdp_quantity'])) { return $valid; }
    $product = wc_get_product($id);
    if ($product && $product->is_type('simple') && ($quantity < 1 || $quantity > remaining($product))) {
        wc_add_notice('La cantidad seleccionada ya no está disponible. Revisa tu carrito y las existencias del producto.', 'error');
        return false;
    }
    return $valid;
}, 20, 3);

// Server-rendered state also works before the cart store finishes loading.
add_filter('render_block_woocommerce/add-to-cart-form',static function(string $html):string {
    if(!is_product()){return $html;}
    $product=wc_get_product(get_queried_object_id());
    if(!$product || !$product->is_type('simple') || !$product->is_in_stock() || remaining($product)>0){return $html;}
    $tags=new \WP_HTML_Tag_Processor($html);
    while($tags->next_tag('BUTTON')){if($tags->get_attribute('type')==='submit'){$tags->set_attribute('hidden',true);$tags->set_attribute('disabled',true);}}
    return $tags->get_updated_html();
},45);

add_filter('woocommerce_product_tabs', static function (array $tabs): array {
    global $product;
    unset($tabs['additional_information']);
    $book = is_book($product);
    if ($book && isset($tabs['description'])) { $tabs['description']['title'] = 'Sinopsis'; }
    $tabs['ellamara_details'] = ['title' => $book ? 'Detalles del libro' : 'Detalles del producto', 'priority' => 20, 'callback' => __NAMESPACE__ . '\\details'];
    if ($product->get_meta('_ellamara_editorial_review')) {
        $tabs['ellamara_editorial'] = ['title' => 'Opiniones editoriales', 'priority' => 25, 'callback' => __NAMESPACE__ . '\\review'];
    }
    if (trim((string) $product->get_meta('_ellamara_author_bio')) !== '') {
        $tabs['ellamara_author'] = ['title' => 'Biografía del autor', 'priority' => 27, 'callback' => __NAMESPACE__ . '\\author_bio'];
    }
    return $tabs;
}, 30);
add_filter('woocommerce_product_description_heading', static function (string $heading): string {
    global $product;
    return $product instanceof \WC_Product && is_book($product) ? 'Sinopsis' : $heading;
});
/** Preserve the precision of bibliographic dates instead of inventing a day. */
function publication_date(string $value): string {
    if (!preg_match('/^(\d{4})(?:-(\d{2})(?:-(\d{2}))?)?$/D', $value, $parts)) { return $value; }
    $year = (int) $parts[1];
    if (!isset($parts[2])) { return $parts[1]; }
    $month = (int) $parts[2];
    $day = isset($parts[3]) ? (int) $parts[3] : 1;
    if (!checkdate($month, $day, $year)) { return $value; }
    $months = [1=>'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    return (isset($parts[3]) ? $day . ' de ' : '') . $months[$month] . ' de ' . $parts[1];
}
function bookmark_details(\WC_Product $product): bool {
    if (!$product->is_type('variable') || !has_term('marcapaginas', 'product_cat', $product->get_id())) {
        return false;
    }

    $cards = '';
    foreach ($product->get_visible_children() as $id) {
        $variation = wc_get_product($id);
        $collection = $variation ? $variation->get_attribute('coleccion') : '';
        if ($collection === '') { continue; }

        $image = wp_get_attachment_image($variation->get_image_id(), 'medium', false, ['loading' => 'lazy']);
        $cards .= '<li>' . $image . '<span>' . esc_html($collection) . '</span></li>';
    }
    if ($cards === '') { return false; }

    echo '<section class="ellamara-bookmark-details" aria-labelledby="ellamara-bookmark-collections">'
        . '<h2 id="ellamara-bookmark-collections">Colecciones</h2>'
        . '<ul class="ellamara-bookmark-collections">' . $cards . '</ul>';
    if ($product->get_sku()) {
        echo '<p class="ellamara-bookmark-reference">Referencia: ' . esc_html($product->get_sku()) . '</p>';
    }
    echo '</section>';
    return true;
}

function details(): void {
    global $product;
    if (bookmark_details($product)) { return; }
    $book = is_book($product);
    echo '<h2>' . esc_html($book ? 'Detalles del libro' : 'Detalles del producto') . '</h2><table class="ellamara-book-details"><caption class="screen-reader-text">Ficha del producto</caption><tbody>';
    foreach (fields() as $key => $label) {
        $value = trim((string) $product->get_meta('_ellamara_book_' . $key));
        if ($value === '' || strcasecmp($value, 'Por confirmar') === 0) { continue; }
        if ($key === 'date') { $value = publication_date((string) $value); }
        echo '<tr><th scope="row">' . esc_html($label) . '</th><td>' . esc_html($value) . '</td></tr>';
    }
    if ($product->get_sku()) { echo '<tr><th scope="row">SKU</th><td>' . esc_html($product->get_sku()) . '</td></tr>'; }
    $size = trim((string) $product->get_meta('_ellamara_book_size'));
    if ($size !== '') { echo '<tr><th scope="row">Tamaño (alto × ancho)</th><td>' . esc_html($size) . '</td></tr>'; }
    elseif ($product->has_dimensions()) { echo '<tr><th scope="row">Dimensiones</th><td>' . esc_html(wc_format_dimensions($product->get_dimensions(false))) . '</td></tr>'; }
    echo '</tbody></table>';
    // Preserve existing WooCommerce attributes and weight, if configured.
    wc_display_product_attributes($product);
}
function review(): void {
    global $product;
    echo '<h2>Opiniones editoriales</h2>';
    $text = $product->get_meta('_ellamara_editorial_review');
    if (!$text) { return; }
    $source = $product->get_meta('_ellamara_review_source');
    $url = $product->get_meta('_ellamara_review_url');
    $quotes = preg_split('/\R\s*\R/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
    $total = count($quotes);
    echo '<section class="ellamara-review-carousel" aria-label="Opiniones editoriales" data-review-carousel>';
    echo '<div class="ellamara-review-controls" hidden><span data-review-position aria-live="polite" aria-atomic="true"></span><div><button type="button" data-review-prev aria-label="Opinión editorial anterior">←</button><button type="button" data-review-next aria-label="Siguiente opinión editorial">→</button></div></div>';
    echo '<div class="ellamara-review-viewport">';
    foreach ($quotes as $index => $quote) {
        $lines = preg_split('/\R/u', trim($quote));
        $credit = count($lines) > 1 ? array_pop($lines) : $source;
        echo '<div class="ellamara-review-slide" role="group" aria-label="Opinión ' . ($index + 1) . ' de ' . $total . '"><blockquote class="ellamara-editorial-review"><p>' . nl2br(esc_html(implode("\n", $lines))) . '</p>';
        if ($credit) { echo '<cite>' . ($url ? '<a href="' . esc_url($url) . '">' . esc_html($credit) . '</a>' : esc_html($credit)) . '</cite>'; }
        echo '</blockquote></div>';
    }
    echo '</div></section>';
}
function author_bio(): void {
    global $product;
    echo '<h2>Biografía del autor</h2>';
    foreach (preg_split('/\R\s*\R/u', trim((string) $product->get_meta('_ellamara_author_bio')), -1, PREG_SPLIT_NO_EMPTY) as $paragraph) {
        echo '<p>' . nl2br(esc_html($paragraph)) . '</p>';
    }
    // Research provenance remains in product metadata and the CSV, not storefront copy.
}

add_action('wp_enqueue_scripts', static function (): void {
    if (!is_product()) { return; }
    wp_enqueue_script('ellamara-pdp', plugin_dir_url(__FILE__) . 'assets/pdp.js', ['wp-data', 'wc-blocks-data-store'], (string) filemtime(__DIR__ . '/assets/pdp.js'), true);
    wp_enqueue_style('ellamara-editorial-carousel', plugin_dir_url(__FILE__) . 'assets/reviews.css', [], (string) filemtime(__DIR__ . '/assets/reviews.css'));
    wp_enqueue_script('ellamara-editorial-carousel', plugin_dir_url(__FILE__) . 'assets/reviews.js', [], (string) filemtime(__DIR__ . '/assets/reviews.js'), true);
});
