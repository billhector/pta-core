<?php
namespace Pta\Core\Hooks;

defined('ABSPATH') || exit;

final class Module
{
    public function register(): void
    {
        add_filter('upload_mimes',           [$this, 'allow_font_mimes']);
        add_filter('auth_cookie_expiration', [$this, 'four_week_remember_me']);
        add_action('login_footer',           [$this, 'auto_check_remember_me']);
        add_action('save_post',              [$this, 'auto_assign_state_taxonomy']);
    }

    public function allow_font_mimes(array $mimes): array
    {
        $mimes['woff']  = 'font/woff';
        $mimes['woff2'] = 'font/woff2';
        $mimes['ico']   = 'image/ico';
        return $mimes;
    }

    public function four_week_remember_me(int $expires): int
    {
        return 2419200; // 4 weeks
    }

    public function auto_check_remember_me(): void
    {
        echo '<script>'
           . 'document.getElementById("rememberme")?.click();'
           . 'document.getElementById("user_login")?.focus();'
           . '</script>';
    }

    public function auto_assign_state_taxonomy(int $post_id): void
    {
        // The Fluent snippet hardcoded "county-guides" (plural) — confirmed bug; the actual CPT slug is "county-guide" (singular).
        if (get_post_type($post_id) !== 'county-guide') {
            return;
        }
        $state = get_post_meta($post_id, 'state', true);
        if ($state) {
            wp_set_post_terms($post_id, [$state], 'states');
        }
    }
}
