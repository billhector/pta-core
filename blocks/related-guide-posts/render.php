<?php
defined('ABSPATH') || exit;

$limit        = max(1, (int) ($attributes['limit'] ?? 3));
$title_attr   = $attributes['title']       ?? '';
$show_excerpt = (bool) ($attributes['showExcerpt'] ?? true);
$show_date    = (bool) ($attributes['showDate']    ?? false);

$related = \Pta\Core\Blocks\DataAccess::related_guide_posts($limit);
if (empty($related)) {
    return;
}

$post_type = get_post_type();
if ($post_type === 'county-guide') {
    $county = function_exists('rwmb_meta') ? rwmb_meta('county_name') : '';
    $section_title = $county ? "More {$county} Resources" : 'Related Resources';
} else {
    $section_title = $title_attr !== '' ? $title_attr : 'Related Articles';
}

$wrapper = function_exists('get_block_wrapper_attributes')
    ? get_block_wrapper_attributes(['class' => 'related-guide-posts'])
    : 'class="related-guide-posts"';
?>
<div <?php echo $wrapper; ?>>
    <h3 class="related-posts-title"><?php echo esc_html($section_title); ?></h3>
    <div class="related-posts-grid">
        <?php foreach ($related as $p):
            $url   = get_permalink($p->ID);
            $title = get_the_title($p->ID);
            $img   = get_the_post_thumbnail($p->ID, 'medium');
        ?>
            <article class="related-post-item">
                <?php if ($img): ?>
                    <div class="related-post-image"><a href="<?php echo esc_url($url); ?>"><?php echo $img; ?></a></div>
                <?php endif; ?>
                <div class="related-post-content">
                    <div class="related-post-title"><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></div>
                    <?php if ($show_excerpt && ($exc = get_the_excerpt($p->ID))): ?>
                        <p class="related-post-excerpt"><?php echo esc_html($exc); ?></p>
                    <?php endif; ?>
                    <?php if ($show_date): ?>
                        <p class="related-post-date"><?php echo esc_html(get_the_date('F j, Y', $p->ID)); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo esc_url($url); ?>" class="related-post-link">Read More →</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php
