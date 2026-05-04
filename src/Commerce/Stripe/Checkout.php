<?php
namespace Pta\Core\Commerce\Stripe;

defined('ABSPATH') || exit;

final class Checkout
{
    public function __construct(private Client $client) {}

    public function create_session(string $price_id, string $success_url, string $cancel_url): array
    {
        $body = [
            'mode'                       => 'payment',
            'line_items[0][price]'       => $price_id,
            'line_items[0][quantity]'    => 1,
            'success_url'                => $success_url,
            'cancel_url'                 => $cancel_url,
            'allow_promotion_codes'      => 'true',
            'customer_creation'          => 'always',
            'billing_address_collection' => 'auto',
        ];
        return $this->client->post(
            '/checkout/sessions',
            $body,
            idempotency_key: 'pta_' . wp_generate_uuid4()
        );
    }
}
