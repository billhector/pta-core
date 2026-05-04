<?php
/**
 * Test bootstrap: composer autoload + minimum WP constants used by code under test.
 */

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wp/');
}
if (!defined('PTA_R2_BUCKET'))         define('PTA_R2_BUCKET', 'pta-downloads-test');
if (!defined('PTA_R2_ENDPOINT'))       define('PTA_R2_ENDPOINT', 'https://test.r2.cloudflarestorage.com');
if (!defined('PTA_R2_ACCESS_KEY'))     define('PTA_R2_ACCESS_KEY', 'TEST_ACCESS_KEY');
if (!defined('PTA_R2_SECRET_KEY'))     define('PTA_R2_SECRET_KEY', 'TEST_SECRET_KEY');
if (!defined('PTA_MAGIC_LINK_SECRET')) define('PTA_MAGIC_LINK_SECRET', str_repeat('a', 64));

// Test stubs for WP + Meta Box functions used by code under test.
if (!function_exists('rwmb_meta')) {
    function rwmb_meta(string $field): string {
        return match ($field) {
            'county_name' => 'Cook',
            'state_name'  => 'Illinois',
            'guide_price' => '67',
            default       => '',
        };
    }
}
if (!function_exists('get_permalink')) {
    function get_permalink(): string {
        return 'https://propertytaxappealguides.com/illinois/cook-county/';
    }
}
if (!function_exists('home_url')) {
    function home_url(string $p = ''): string {
        return 'https://propertytaxappealguides.com' . $p;
    }
}
if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, int $flags = 0): string {
        return (string) json_encode($data, $flags);
    }
}
if (!function_exists('get_option')) {
    $GLOBALS['_pta_test_options'] = [];
    function get_option(string $k, $default = false) {
        return $GLOBALS['_pta_test_options'][$k] ?? $default;
    }
}
if (!function_exists('update_option')) {
    function update_option(string $k, $v, $autoload = null): bool {
        $GLOBALS['_pta_test_options'][$k] = $v;
        return true;
    }
}

// Routes test stubs (used by CountyResolverTest). All read from $GLOBALS-backed fixtures
// that the test sets up in setUp().
if (!function_exists('get_term_by')) {
    function get_term_by(string $field, string $value, string $taxonomy) {
        foreach ($GLOBALS['_pta_test_terms'] ?? [] as $term) {
            if ($term['taxonomy'] === $taxonomy && ($term[$field] ?? null) === $value) {
                return (object) $term;
            }
        }
        return false;
    }
}
if (!function_exists('get_posts')) {
    function get_posts(array $args): array {
        $name      = $args['name']      ?? null;
        $tax_query = $args['tax_query'] ?? null;
        $term_id   = $tax_query[0]['terms'][0] ?? null;

        $hits = [];
        foreach ($GLOBALS['_pta_test_posts'] ?? [] as $p) {
            if ($name !== null && $p['post_name'] !== $name) continue;
            if ($term_id !== null && !in_array($term_id, $p['state_term_ids'] ?? [], true)) continue;
            $hits[] = $p['ID'];
        }
        return array_slice($hits, 0, $args['posts_per_page'] ?? 10);
    }
}
if (!function_exists('get_the_terms')) {
    function get_the_terms(int $post_id, string $taxonomy) {
        foreach ($GLOBALS['_pta_test_posts'] ?? [] as $p) {
            if ($p['ID'] === $post_id) {
                $out = [];
                foreach ($p['state_term_ids'] ?? [] as $tid) {
                    foreach ($GLOBALS['_pta_test_terms'] ?? [] as $t) {
                        if ($t['term_id'] === $tid) $out[] = (object) $t;
                    }
                }
                return $out ?: false;
            }
        }
        return false;
    }
}
if (!function_exists('get_post_field')) {
    function get_post_field(string $field, int $post_id) {
        foreach ($GLOBALS['_pta_test_posts'] ?? [] as $p) {
            if ($p['ID'] === $post_id) return $p[$field] ?? '';
        }
        return '';
    }
}
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing): bool {
        return $thing instanceof \WP_Error;
    }
}
