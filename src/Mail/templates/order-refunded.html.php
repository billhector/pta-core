<?php
/** @var string $amount_formatted */
/** @var string $support_email */
defined('ABSPATH') || exit;
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Your refund has been processed</title></head>
<body style="font-family: system-ui, -apple-system, sans-serif; max-width:600px; margin:0 auto; padding:24px; color:#1a1a1a; line-height:1.5;">
  <h1 style="font-size:22px; margin:0 0 16px;">Your refund has been processed</h1>
  <p>We've issued a refund of <strong><?php echo esc_html($amount_formatted); ?></strong> back to your original payment method. It typically lands in 5–10 business days.</p>
  <p>Your re-download link is no longer active. If you previously downloaded the guide, please delete your local copy.</p>
  <hr style="border:none; border-top:1px solid #eee; margin:32px 0;">
  <p style="font-size:13px; color:#666;">
    Questions about the refund? Reply to this email or write <a href="mailto:<?php echo esc_attr($support_email); ?>"><?php echo esc_html($support_email); ?></a>.
  </p>
</body></html>
