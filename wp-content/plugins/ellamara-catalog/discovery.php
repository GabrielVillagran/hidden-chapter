<?php
/** Catalog ordering and exact ISBN lookup, scoped to storefront product queries. */
declare(strict_types=1);
namespace Ellamara\Catalog;
defined('ABSPATH') || exit;

function normalized_isbn(string $search): string {
    if (strlen($search) > 80 || !preg_match('/^(?:ISBN(?:-1[03])?:?\s*)?([0-9Xx\s\-\x{2010}-\x{2015}]+)$/iu', trim($search), $match)) { return ''; }
    $isbn = strtoupper(preg_replace('/[^0-9Xx]/', '', $match[1]));
    return preg_match('/^(?:\d{13}|\d{9}[\dX])$/', $isbn) ? $isbn : '';
}

function isbn_search(string $search, \WP_Query $query): string {
    if (is_admin() || wp_doing_ajax() || defined('REST_REQUEST') || !$query->is_search() || !in_array($query->get('post_type'), ['product', ['product']], true)) { return $search; }
    $isbn = normalized_isbn((string) $query->get('s'));
    if (!$isbn || !preg_match('/^\s*AND\s+/i', $search)) { return $search; }
    global $wpdb;
    $native = preg_replace('/^\s*AND\s+/i', '', $search, 1);
    // EXISTS avoids duplicate products even if a legacy import left duplicate meta rows.
    $match = $wpdb->prepare("EXISTS (SELECT 1 FROM {$wpdb->postmeta} AS ellamara_isbn WHERE ellamara_isbn.post_id = {$wpdb->posts}.ID AND ellamara_isbn.meta_key = %s AND REPLACE(REPLACE(UPPER(ellamara_isbn.meta_value), '-', ''), ' ', '') = %s)", '_ellamara_book_isbn', $isbn);
    $search = " AND (({$native}) OR {$match}) ";
    // A metadata match must never bypass WordPress's guest password protection.
    if (!is_user_logged_in()) { $search .= " AND ({$wpdb->posts}.post_password = '') "; }
    return $search;
}
add_filter('posts_search', __NAMESPACE__ . '\\isbn_search', 30, 2);

function available_first(array $clauses, \WP_Query $query): array {
    if (is_admin() || wp_doing_ajax() || defined('REST_REQUEST') || !$query->get('ellamara_catalog_listing')) { return $clauses; }
    global $wpdb;
    // A stable title order applies to the entire homepage collection, before pagination.
    if ($query->get('ellamara_home_alphabetical')) {
        $clauses['orderby'] = "{$wpdb->posts}.post_title ASC, {$wpdb->posts}.ID ASC";
        return $clauses;
    }
    // WooCommerce's indexed one-row-per-product lookup keeps pagination and totals intact.
    $clauses['join'] .= " LEFT JOIN {$wpdb->prefix}wc_product_meta_lookup AS ellamara_stock_lookup ON {$wpdb->posts}.ID = ellamara_stock_lookup.product_id ";
    $original = trim($clauses['orderby']);
    $clauses['orderby'] = "CASE ellamara_stock_lookup.stock_status WHEN 'instock' THEN 0 WHEN 'onbackorder' THEN 1 ELSE 2 END ASC, "
        . ($original ? $original . ', ' : '') . "{$wpdb->posts}.ID DESC";
    return $clauses;
}
add_filter('posts_clauses', __NAMESPACE__ . '\\available_first', 100, 2);
