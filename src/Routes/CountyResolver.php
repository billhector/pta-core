<?php
namespace Pta\Core\Routes;

defined('ABSPATH') || exit;

/**
 * Resolves /{state}/{county}/ → county-guide post id, or null if no match.
 */
final class CountyResolver
{
    public function resolve(string $state_slug, string $county_slug): ?int
    {
        $term = get_term_by('slug', $state_slug, 'state');
        if (!$term || is_wp_error($term)) {
            return null;
        }
        $posts = get_posts([
            'post_type'      => 'county-guide',
            'name'           => $county_slug,
            'tax_query'      => [[
                'taxonomy' => 'state',
                'field'    => 'term_id',
                'terms'    => [$term->term_id],
            ]],
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'post_status'    => 'publish',
        ]);
        return !empty($posts) ? (int) $posts[0] : null;
    }

    public function canonical_url(int $post_id): ?string
    {
        $terms = get_the_terms($post_id, 'state');
        if (!$terms || is_wp_error($terms)) {
            return null;
        }
        $state_slug  = $terms[0]->slug;
        $county_slug = get_post_field('post_name', $post_id);
        if (!$county_slug) {
            return null;
        }
        return home_url("/{$state_slug}/{$county_slug}/");
    }
}
