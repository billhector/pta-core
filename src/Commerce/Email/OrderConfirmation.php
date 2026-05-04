<?php
namespace Pta\Core\Commerce\Email;

use Pta\Core\Commerce\MagicLink\Token;
use Pta\Core\Commerce\OrderStore;
use Pta\Core\Commerce\R2\Signer;
use Pta\Core\Mail\Client;
use Pta\Core\Mail\Send;
use Pta\Core\Mail\Templates;

defined('ABSPATH') || exit;

final class OrderConfirmation
{
    public function send(int $order_id, string $email, string $r2_key): void
    {
        $signer = Signer::from_constants();
        $one_time_url = $signer->presign_get($r2_key, 600);

        $magic_token = Token::mint($order_id, $email);
        $magic_url   = home_url('/redownload?token=' . rawurlencode($magic_token));

        $order = OrderStore::find_by_id($order_id);
        $amount_formatted = '$' . number_format(($order['amount_total'] ?? 0) / 100, 2);

        $vars = [
            'one_time_url'     => $one_time_url,
            'magic_url'        => $magic_url,
            'file_name'        => basename($r2_key),
            'amount_formatted' => $amount_formatted,
            'support_email'    => 'reports@propertytaxappealguides.com',
        ];

        $html = Templates::render('order-confirmation.html', $vars);
        $text = Templates::render('order-confirmation.txt', $vars);

        try {
            (new Send(Client::from_constants()))->send_html(
                $email, '', 'Your property tax appeal guide is ready', $html, $text,
                ['custom_id' => 'order-' . $order_id]
            );
        } catch (\Throwable $e) {
            error_log('pta-core Send-API fallback to wp_mail: ' . $e->getMessage());
            wp_mail($email, 'Your property tax appeal guide is ready', $text, [
                'Content-Type: text/plain; charset=UTF-8',
            ]);
        }
    }
}
