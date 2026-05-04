<?php
/** @var string $one_time_url */
/** @var string $magic_url */
/** @var string $file_name */
/** @var string $amount_formatted */
/** @var string $support_email */
defined('ABSPATH') || exit;
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Your guide is ready</title></head>
<body style="font-family: system-ui, -apple-system, sans-serif; max-width:600px; margin:0 auto; padding:24px; color:#1a1a1a; line-height:1.5;">
  <h1 style="font-size:22px; margin:0 0 16px;">Thanks for your order</h1>
  <p>Your <strong><?php echo esc_html($file_name); ?></strong> is ready to download.</p>
  <p style="margin:32px 0;">
    <a href="<?php echo esc_url($one_time_url); ?>" style="display:inline-block; padding:14px 24px; background:#0a5; color:#fff; text-decoration:none; border-radius:6px; font-weight:600;">
      Download now
    </a>
    <br><small style="color:#666; display:inline-block; margin-top:8px;">(this one-time link expires in 10 minutes)</small>
  </p>
  <p style="margin:24px 0;">You can also re-download anytime from this link, which works as long as your order is active:</p>
  <p style="word-break:break-all;"><a href="<?php echo esc_url($magic_url); ?>"><?php echo esc_html($magic_url); ?></a></p>
  <p style="margin-top:24px;"><strong>Order amount:</strong> <?php echo esc_html($amount_formatted); ?></p>
  <hr style="border:none; border-top:1px solid #eee; margin:32px 0;">
  <p style="font-size:13px; color:#666;">
    Need help? Reply to this email or write <a href="mailto:<?php echo esc_attr($support_email); ?>"><?php echo esc_html($support_email); ?></a>.
  </p>
</body></html>
