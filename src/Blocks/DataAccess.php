<?php
namespace Pta\Core\Blocks;

defined('ABSPATH') || exit;

/**
 * Cached data accessors ported from Fluent Snippets `2-functions.php`.
 * Each method preserves the original cache key, TTL, and data shape so block
 * renders are 1:1 substitutes for their shortcode predecessors.
 */
final class DataAccess
{
    public static function county_contacts(): array
    {
        $post_id = get_the_ID();
        $cache_key = "county_contacts_{$post_id}";
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        $fields = [
            'county_assessor_office_name',
            'county_assessor_office_phone',
            'county_assessor_office_website',
            'county_appeal_board_name',
            'county_appeal_board_phone',
            'county_appeal_board_website',
        ];
        $data = [];
        foreach ($fields as $f) {
            $data[$f] = function_exists('rwmb_meta') ? rwmb_meta($f) : '';
        }
        set_transient($cache_key, $data, 4 * HOUR_IN_SECONDS);
        return $data;
    }

    public static function municipalities(string $county): array
    {
        $cache_key = 'municipalities_' . sanitize_key($county);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        $ids = get_posts([
            'post_type'              => 'municipalities',
            'posts_per_page'         => -1,
            'meta_query'             => [['key' => 'county', 'value' => $county, 'compare' => 'LIKE']],
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
        ]);
        if (empty($ids)) {
            set_transient($cache_key, [], 6 * HOUR_IN_SECONDS);
            return [];
        }
        $rows = [];
        foreach ($ids as $id) {
            $m = get_post_meta($id);
            $rows[] = [
                'id'              => $id,
                'name'            => $m['municipality_name'][0] ?? '',
                'assessment_year' => $m['assessment_year'][0] ?? '',
                'deadline_date'   => $m['deadline_date'][0] ?? '',
                'cycle_years'     => $m['cycle_years'][0] ?? '',
            ];
        }
        set_transient($cache_key, $rows, 6 * HOUR_IN_SECONDS);
        return $rows;
    }

    public static function county_guide_product_info(): array|false
    {
        $post_id = get_the_ID();
        $cache_key = "product_info_{$post_id}";
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        $product_id = function_exists('rwmb_meta') ? rwmb_meta('edd_product_id') : null;
        if (!$product_id || !function_exists('edd_get_download_price')) {
            return false;
        }
        $current_price = edd_get_download_price($product_id);
        $sale_price    = get_post_meta($product_id, 'edd_sale_price', true);
        $is_on_sale    = !empty($sale_price);
        $regular_price = $is_on_sale ? 97 : $current_price;

        $info = [
            'id'                      => $product_id,
            'is_on_sale'              => $is_on_sale,
            'regular_price'           => $regular_price,
            'sale_price'              => $sale_price,
            'current_price'           => $current_price,
            'formatted_regular_price' => edd_currency_filter(edd_format_amount($regular_price)),
            'formatted_sale_price'    => $is_on_sale ? edd_currency_filter(edd_format_amount($sale_price)) : null,
            'formatted_current_price' => edd_currency_filter(edd_format_amount($current_price)),
            'purchase_url'            => site_url() . '?edd_action=add_to_cart&download_id=' . $product_id,
            'is_purchased'            => function_exists('edd_has_user_purchased')
                ? edd_has_user_purchased(get_current_user_id(), $product_id)
                : false,
        ];
        set_transient($cache_key, $info, HOUR_IN_SECONDS);
        return $info;
    }

    public static function related_guide_posts(int $limit = 3): array
    {
        $post_id = get_the_ID();
        $post_type = get_post_type();
        $cache_key = "related_posts_{$post_type}_{$post_id}_{$limit}";
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }
        $posts = [];
        if ($post_type === 'county-guide') {
            $county_name = function_exists('rwmb_meta') ? rwmb_meta('county_name') : '';
            $state_name  = function_exists('rwmb_meta') ? rwmb_meta('state') : '';
            if ($county_name && $state_name) {
                $county_tag = strtolower(str_replace([' ', ','], ['-', ''], $county_name));
                $posts = get_posts([
                    'post_type' => 'post', 'posts_per_page' => $limit, 'post_status' => 'publish',
                    'exclude' => [$post_id], 'tag' => $county_tag, 'no_found_rows' => true,
                ]);
                if (count($posts) < $limit) {
                    $existing = wp_list_pluck($posts, 'ID');
                    $existing[] = $post_id;
                    $posts = array_merge($posts, get_posts([
                        'post_type' => 'post', 'posts_per_page' => $limit - count($posts), 'post_status' => 'publish',
                        'exclude' => $existing, 'tag' => 'property-tax-appeals',
                        'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true,
                    ]));
                }
            }
        } else {
            $tags = get_the_tags();
            if ($tags && !is_wp_error($tags)) {
                $specific = [];
                $has_generic = false;
                foreach ($tags as $t) {
                    if ($t->slug === 'property-tax-appeals') { $has_generic = true; }
                    else { $specific[] = $t->term_id; }
                }
                if (!empty($specific)) {
                    $posts = get_posts([
                        'post_type' => 'post', 'posts_per_page' => $limit, 'post_status' => 'publish',
                        'exclude' => [$post_id], 'tag__in' => $specific,
                        'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true,
                    ]);
                }
                if (count($posts) < $limit && $has_generic) {
                    $existing = wp_list_pluck($posts, 'ID');
                    $existing[] = $post_id;
                    $posts = array_merge($posts, get_posts([
                        'post_type' => 'post', 'posts_per_page' => $limit - count($posts), 'post_status' => 'publish',
                        'exclude' => $existing, 'tag' => 'property-tax-appeals',
                        'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true,
                    ]));
                }
            }
        }
        $ttl = ($post_type === 'county-guide') ? 12 * HOUR_IN_SECONDS : 6 * HOUR_IN_SECONDS;
        set_transient($cache_key, $posts, $ttl);
        return $posts;
    }
}
