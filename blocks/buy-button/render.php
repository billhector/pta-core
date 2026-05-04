<?php
defined('ABSPATH') || exit;

$price_id = $attributes['priceId'] ?? '';
$label    = $attributes['label']   ?? 'Buy now';

if ($price_id === '') {
    if (current_user_can('edit_posts')) {
        echo '<p style="color:#a00">[buy-button: missing priceId attribute]</p>';
    }
    return;
}

$rest_url = esc_url(rest_url('pta/v1/checkout'));
$nonce    = wp_create_nonce('wp_rest');
$btn_id   = 'pta-buy-' . wp_generate_uuid4();
?>
<button type="button" id="<?php echo esc_attr($btn_id); ?>" class="pta-buy-button"
        data-price="<?php echo esc_attr($price_id); ?>"
        data-rest="<?php echo $rest_url; ?>"
        data-nonce="<?php echo esc_attr($nonce); ?>">
    <?php echo esc_html($label); ?>
</button>
<script>
(function(){
    var b = document.getElementById(<?php echo wp_json_encode($btn_id); ?>);
    if (!b) return;
    b.addEventListener('click', async function(){
        b.disabled = true;
        try {
            var r = await fetch(b.dataset.rest, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': b.dataset.nonce },
                body: JSON.stringify({ price_id: b.dataset.price }),
            });
            var j = await r.json();
            if (j.checkout_url) window.location = j.checkout_url;
            else { alert('Checkout failed: ' + (j.error || 'unknown')); b.disabled = false; }
        } catch (err) { alert('Network error: ' + err.message); b.disabled = false; }
    });
})();
</script>
<?php
