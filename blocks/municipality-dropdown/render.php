<?php
defined('ABSPATH') || exit;

$county = $attributes['county'] ?? '';
if ($county === '') {
    if (current_user_can('edit_posts')) {
        echo '<p style="color:#a00">[municipality-dropdown: missing county attribute]</p>';
    }
    return;
}

$municipalities = \Pta\Core\Blocks\DataAccess::municipalities($county);
if (empty($municipalities)) {
    return;
}

$options = [];
foreach ($municipalities as $m) {
    if (empty($m['name'])) continue;
    $next = ($m['assessment_year'] && $m['cycle_years'])
        ? ((int) $m['assessment_year'] + (int) $m['cycle_years'])
        : '';
    $deadline = $m['deadline_date'] ? date('F j, Y', strtotime($m['deadline_date'])) : '';
    $options[] = sprintf(
        '<option value="%d" data-year="%s" data-deadline="%s" data-next="%s">%s</option>',
        (int) $m['id'],
        esc_attr($m['assessment_year']),
        esc_attr($deadline),
        esc_attr($next),
        esc_html($m['name'])
    );
}

$dropdown_id = 'municipality-dropdown-' . uniqid();
$info_id     = 'municipality-info-' . uniqid();

$wrapper = function_exists('get_block_wrapper_attributes')
    ? get_block_wrapper_attributes(['class' => 'municipality-selector-hero'])
    : 'class="municipality-selector-hero"';
?>
<div <?php echo $wrapper; ?>>
    <p class="municipality-label">Select your municipality for specific deadlines:</p>
    <select id="<?php echo esc_attr($dropdown_id); ?>" class="municipality-select">
        <option value="">Choose your city or township</option>
        <?php echo implode('', $options); ?>
    </select>
    <div id="<?php echo esc_attr($info_id); ?>" class="municipality-results" style="display:none;">
        <p><strong>Your assessment year:</strong> <span class="selected-year"></span></p>
        <p><strong>Your appeal deadline:</strong> <span class="selected-deadline"></span></p>
        <p><strong>Next assessment:</strong> <span class="selected-next"></span></p>
    </div>
</div>
<script>
(function(){
    var sel = document.getElementById(<?php echo wp_json_encode($dropdown_id); ?>);
    var info = document.getElementById(<?php echo wp_json_encode($info_id); ?>);
    if (!sel || !info) return;
    sel.addEventListener('change', function() {
        var opt = sel.options[sel.selectedIndex];
        if (opt && opt.value) {
            info.querySelector('.selected-year').textContent     = opt.dataset.year || '';
            info.querySelector('.selected-deadline').textContent = opt.dataset.deadline || '';
            info.querySelector('.selected-next').textContent     = opt.dataset.next || '';
            info.style.display = '';
        } else {
            info.style.display = 'none';
        }
    });
})();
</script>
<?php
