<?php
/**
 * Plugin Name:       PTA Core
 * Plugin URI:        https://github.com/billhector/pta-core
 * Description:       PropertyTaxAppealGuides.com core: Stripe commerce, R2 delivery, magic-link redownload, Mailjet email, dynamic blocks, schema, and site hooks.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.2
 * Author:            Bill Hector
 * Author URI:        https://propertytaxappealguides.com
 * License:           GPL v2 or later
 * Text Domain:       pta-core
 */

defined('ABSPATH') || exit;

define('PTA_CORE_VERSION', '0.1.0');
define('PTA_CORE_FILE', __FILE__);
define('PTA_CORE_DIR', plugin_dir_path(__FILE__));
define('PTA_CORE_URL', plugin_dir_url(__FILE__));

require_once PTA_CORE_DIR . 'autoload.php';

add_action('plugins_loaded', static function () {
    \Pta\Core\Bootstrap::init();
});

register_activation_hook(__FILE__, static function () {
    update_option('pta_core_activated_at', time());
    \Pta\Core\Commerce\OrderStore::install_schema();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, static function () {
    flush_rewrite_rules();
});
