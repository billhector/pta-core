<?php
/** @var string $one_time_url */
/** @var string $magic_url */
/** @var string $file_name */
/** @var string $amount_formatted */
/** @var string $support_email */
defined('ABSPATH') || exit;
?>
Thanks for your order!

Your <?php echo $file_name; ?> is ready to download.

One-time download (link expires in 10 minutes):
<?php echo $one_time_url; ?>


Re-download anytime from this link (works as long as your order is active):
<?php echo $magic_url; ?>


Order amount: <?php echo $amount_formatted; ?>


Need help? Reply to this email or write <?php echo $support_email; ?>.
