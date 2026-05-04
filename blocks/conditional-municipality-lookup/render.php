<?php
defined('ABSPATH') || exit;

if (get_post_type() !== 'county-guide') {
    return;
}

$post_id   = get_the_ID();
$cache_key = "conditional_lookup_{$post_id}";
$cached    = get_transient($cache_key);
if ($cached !== false) {
    echo $cached;
    return;
}

$has_lookup  = get_post_meta($post_id, 'has_municipality_lookup', true);
$county_name = get_post_meta($post_id, 'county_name', true);

$result = '';
if ($has_lookup && $county_name) {
    // Render the municipality-dropdown block server-side with the county attribute.
    $result = render_block([
        'blockName' => 'pta-core/municipality-dropdown',
        'attrs'     => ['county' => $county_name],
        'innerBlocks' => [],
        'innerHTML'   => '',
        'innerContent'=> [],
    ]);
}

set_transient($cache_key, $result, 2 * HOUR_IN_SECONDS);
echo $result;
