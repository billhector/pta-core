<?php
namespace Pta\Core\Analytics;

defined('ABSPATH') || exit;

final class Ga4MeasurementProtocol
{
    private const ENDPOINT = 'https://www.google-analytics.com/mp/collect';

    public function track_purchase(int $order_id, string $email, int $amount_cents, string $currency, string $product_id): void
    {
        if (PTA_GA4_MEASUREMENT_ID === '' || PTA_GA4_API_SECRET === '') {
            return;
        }

        $payload = [
            'client_id' => $this->derive_client_id($email),
            'events'    => [[
                'name'   => 'purchase',
                'params' => [
                    'transaction_id' => 'pta_' . $order_id,
                    'value'          => $amount_cents / 100,
                    'currency'       => strtoupper($currency),
                    'items'          => [[
                        'item_id'   => $product_id,
                        'item_name' => $product_id,
                        'price'     => $amount_cents / 100,
                        'quantity'  => 1,
                    ]],
                ],
            ]],
        ];

        $url = add_query_arg(
            ['measurement_id' => PTA_GA4_MEASUREMENT_ID, 'api_secret' => PTA_GA4_API_SECRET],
            self::ENDPOINT
        );

        wp_remote_post($url, [
            'headers'  => ['Content-Type' => 'application/json'],
            'body'     => wp_json_encode($payload),
            'timeout'  => 5,
            'blocking' => false,
        ]);
    }

    public function derive_client_id(string $email): string
    {
        $salt = get_option('pta_ga4_client_id_salt');
        if (!$salt) {
            $salt = bin2hex(random_bytes(16));
            update_option('pta_ga4_client_id_salt', $salt, false);
        }
        return hash('sha256', $salt . strtolower($email));
    }
}
