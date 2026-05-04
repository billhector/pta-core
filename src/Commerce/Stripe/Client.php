<?php
namespace Pta\Core\Commerce\Stripe;

defined('ABSPATH') || exit;

final class Client
{
    private const API_BASE = 'https://api.stripe.com/v1';

    public function post(string $path, array $body, ?string $idempotency_key = null): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . PTA_STRIPE_SECRET_KEY,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];
        if ($idempotency_key !== null) {
            $headers['Idempotency-Key'] = $idempotency_key;
        }

        $response = wp_remote_post(self::API_BASE . $path, [
            'headers' => $headers,
            'body'    => http_build_query($body),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            throw new \RuntimeException('Stripe HTTP error: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 400) {
            $msg = $data['error']['message'] ?? 'unknown';
            throw new \RuntimeException("Stripe API {$code}: {$msg}");
        }
        return is_array($data) ? $data : [];
    }

    public function get(string $path): array
    {
        $response = wp_remote_get(self::API_BASE . $path, [
            'headers' => ['Authorization' => 'Bearer ' . PTA_STRIPE_SECRET_KEY],
            'timeout' => 15,
        ]);
        if (is_wp_error($response)) {
            throw new \RuntimeException('Stripe HTTP error: ' . $response->get_error_message());
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($data) ? $data : [];
    }
}
