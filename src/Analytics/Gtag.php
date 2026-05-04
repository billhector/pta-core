<?php
namespace Pta\Core\Analytics;

defined('ABSPATH') || exit;

/**
 * Client-side GA4 gtag.js loader. Emits in wp_head when PTA_GA4_MEASUREMENT_ID is set.
 *
 * Plan D may relocate this to the pta-blocks theme's <head> partial; until then this
 * provides client-side GA4 coverage for the Elementor pages still running pre-cutover.
 */
final class Gtag
{
    public function emit(): void
    {
        if (PTA_GA4_MEASUREMENT_ID === '' || is_admin()) {
            return;
        }
        $id = esc_js(PTA_GA4_MEASUREMENT_ID);
        ?>
<!-- Google tag (gtag.js) — pta-core -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $id; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo $id; ?>');
</script>
        <?php
    }
}
