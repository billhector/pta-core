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
