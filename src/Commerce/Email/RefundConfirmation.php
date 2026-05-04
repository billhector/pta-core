<?php
namespace Pta\Core\Commerce\Email;

use Pta\Core\Commerce\OrderStore;
use Pta\Core\Mail\Client;
use Pta\Core\Mail\Send;
use Pta\Core\Mail\Templates;

defined('ABSPATH') || exit;

final class RefundConfirmation
{
    public function send(string $payment_intent): void
    {
        global $wpdb;
        $order = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . OrderStore::table() . " WHERE stripe_payment_intent = %s LIMIT 1",
                $payment_intent
            ),
            ARRAY_A
        );
        if (!$order) {
            return;
        }

        $vars = [
            'amount_formatted' => '$' . number_format(((int) $order['amount_total']) / 100, 2),
            'support_email'    => 'reports@propertytaxappealguides.com',
        ];
        $html = Templates::render('order-refunded.html', $vars);
        $text = Templates::render('order-refunded.txt', $vars);

        try {
            (new Send(Client::from_constants()))->send_html(
                $order['customer_email'], '', 'Your refund has been processed', $html, $text,
                ['custom_id' => 'refund-' . $order['id']]
            );
        } catch (\Throwable $e) {
            error_log('pta-core refund email Send-API fallback: ' . $e->getMessage());
            wp_mail($order['customer_email'], 'Your refund has been processed', $text);
        }
    }
}
