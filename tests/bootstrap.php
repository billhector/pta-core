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
