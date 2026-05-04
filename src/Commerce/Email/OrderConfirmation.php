<?php
namespace Pta\Core\Commerce\Email;

use Pta\Core\Commerce\MagicLink\Token;
use Pta\Core\Commerce\R2\Signer;

defined('ABSPATH') || exit;

final class OrderConfirmation
{
    public function send(int $order_id, string $email, string $r2_key): void
    {
        $signer = Signer::from_constants();
        $one_time_url = $signer->presign_get($r2_key, 600);

        $magic_token = Token::mint($order_id, $email);
        $magic_url   = home_url('/redownload?token=' . rawurlencode($magic_token));

        $subject = 'Your property tax appeal guide is ready';
        $body    = "Thanks for your order!\n\n"
                 . "One-time download (valid 10 minutes):\n{$one_time_url}\n\n"
                 . "Re-download anytime here:\n{$magic_url}\n\n"
                 . "Save this email or bookmark the re-download link — it works as long as your order is active.\n";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        wp_mail($email, $subject, $body, $headers);
    }
}
