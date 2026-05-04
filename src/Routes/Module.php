<?php
namespace Pta\Core\Routes;

defined('ABSPATH') || exit;

final class Module
{
    /**
     * First-segments that must NOT be matched by the /{state}/{county}/ rewrite.
     * Anything in this set falls through to WP's default routing.
     */
    private const RESERVED_FIRST_SEGMENTS = [
        'wp-admin', 'wp-login.php', 'wp-content', 'wp-includes', 'wp-json',
        'feed', 'comments', 'sitemap_index.xml', 'sitemap.xml', 'robots.txt',
        'blog', 'category', 'tag', 'author', 'search', 'page',
        'guides',          // legacy CPT slug; handled by 301 below
        'redownload',      // pta-core magic-link page (Plan B)
        'checkout', 'receipt', 'transaction-failed', 'order-history',
        'confirmation', 'products', 'invoice', // EDD pages dropped at Plan E
        'about', 'contact', 'terms-and-conditions', 'privacy-policy',
        'how-to-appeal-property-taxes-step-by-step-process-guide',
        'refund-policy',
    ];

    public function register(): void
    {
        add_action('init',              [$this, 'register_rewrites']);
        add_filter('query_vars',        [$this, 'add_query_vars']);
        add_action('parse_request',     [$this, 'on_parse_request']);
        add_action('template_redirect', [$this, 'maybe_redirect_legacy_url']);
        add_filter('post_type_link',    [$this, 'filter_post_type_link'], 10, 2);
    }

    public function register_rewrites(): void
    {
        // 'top' priority so we beat WP's generic attachment rule (`[^/]+/([^/]+)/?$`),
        // BUT a negative lookahead excludes reserved first-segments so legacy /guides/{slug}/
        // and other built-in routes still resolve normally.
        $reserved = implode('|', array_map('preg_quote', self::RESERVED_FIRST_SEGMENTS));
        add_rewrite_rule(
            '^(?!(?:' . $reserved . ')/)([^/]+)/([^/]+)/?$',
            'index.php?pta_state=$matches[1]&pta_county=$matches[2]',
            'top'
        );
    }

    public function add_query_vars(array $vars): array
    {
        $vars[] = 'pta_state';
        $vars[] = 'pta_county';
        return $vars;
    }

    public function on_parse_request(\WP $wp): void
    {
        $state  = $wp->query_vars['pta_state']  ?? '';
        $county = $wp->query_vars['pta_county'] ?? '';
        if ($state === '' || $county === '') {
            return;
        }
        if (in_array($state, self::RESERVED_FIRST_SEGMENTS, true)) {
            // Reserved first segment — let WP's default routing handle it
            unset($wp->query_vars['pta_state'], $wp->query_vars['pta_county']);
            return;
        }
        $post_id = (new CountyResolver())->resolve($state, $county);
        if ($post_id) {
            $wp->query_vars = [
                'p'         => $post_id,
                'post_type' => 'county-guide',
            ];
            return;
        }
        // No county-guide match: clear our query vars, let WP fall through (likely 404)
        unset($wp->query_vars['pta_state'], $wp->query_vars['pta_county']);
    }

    public function maybe_redirect_legacy_url(): void
    {
        if (!is_singular('county-guide')) {
            return;
        }
        $request = $_SERVER['REQUEST_URI'] ?? '/';
        if (strpos($request, '/guides/') !== 0) {
            return;
        }
        $canonical = (new CountyResolver())->canonical_url(get_the_ID());
        if ($canonical) {
            wp_safe_redirect($canonical, 301);
            exit;
        }
    }

    public function filter_post_type_link(string $url, \WP_Post $post): string
    {
        if ($post->post_type !== 'county-guide') {
            return $url;
        }
        $canonical = (new CountyResolver())->canonical_url($post->ID);
        return $canonical ?: $url;
    }
}
