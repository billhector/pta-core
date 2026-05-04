<?php
namespace Pta\Core\Commerce\Stripe;

use Pta\Core\Commerce\OrderStore;

defined('ABSPATH') || exit;

final class Webhook
{
    private const TOLERANCE_SECONDS = 300;

    public function handle(\WP_REST_Request $req): \WP_REST_Response
    {
        $payload   = $req->get_body();
        $signature = $req->get_header('stripe_signature') ?? $req->get_header('Stripe-Signature') ?? '';

        if (!$this->verify_signature($payload, $signature, PTA_STRIPE_WEBHOOK_SECRET)) {
            return new \WP_REST_Response(['error' => 'invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        if (!is_array($event) || !isset($event['type'])) {
            return new \WP_REST_Response(['error' => 'malformed event'], 400);
        }

        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handle_session_completed($event['data']['object'] ?? []);
                break;
            case 'charge.refunded':
                $this->handle_charge_refunded($event['data']['object'] ?? []);
                break;
            default:
                break;
        }

        return new \WP_REST_Response(['ok' => true], 200);
    }

    public function verify_signature(string $payload, string $sig_header, string $secret): bool
    {
        if ($sig_header === '' || $secret === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $sig_header) as $kv) {
            $piece = array_map('trim', explode('=', $kv, 2));
            if (count($piece) !== 2) {
                continue;
            }
            $parts[$piece[0]][] = $piece[1];
        }
        $timestamp = (int) ($parts['t'][0] ?? 0);
        $sigs      = $parts['v1'] ?? [];

        if ($timestamp <= 0 || empty($sigs)) {
            return false;
        }
        if (abs(time() - $timestamp) > self::TOLERANCE_SECONDS) {
            return false;
        }
        $signed_payload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signed_payload, $secret);
        foreach ($sigs as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }
        return false;
    }

    private function handle_session_completed(array $session): void
    {
        if (empty($session['id'])) {
            return;
        }
        $email = $session['customer_email'] ?? ($session['customer_details']['email'] ?? '');
        if ($email === '') {
            return;
        }
        if (OrderStore::find_by_session($session['id'])) {
            return;
        }

        $client = new Client();
        $line_items = $client->get('/checkout/sessions/' . urlencode($session['id']) . '/line_items?limit=1');
        $product_id = $line_items['data'][0]['price']['product'] ?? null;
        if (!$product_id) {
            return;
        }
        $product = $client->get('/products/' . urlencode($product_id));
        $r2_key  = $product['metadata']['r2_key'] ?? null;
        if (!$r2_key) {
            return;
        }

        $order_id = OrderStore::insert_paid([
            'stripe_session_id'     => $session['id'],
            'stripe_payment_intent' => $session['payment_intent'] ?? null,
            'customer_email'        => strtolower($email),
            'amount_total'          => $session['amount_total'] ?? 0,
            'currency'              => $session['currency'] ?? 'usd',
            'stripe_product_id'     => $product_id,
            'r2_key'                => $r2_key,
        ]);

        do_action('pta_core_order_paid', $order_id, strtolower($email), $r2_key);
    }

    private function handle_charge_refunded(array $charge): void
    {
        $pi = $charge['payment_intent'] ?? '';
        if ($pi !== '') {
            OrderStore::mark_refunded($pi);
            do_action('pta_core_order_refunded', $pi);
        }
    }
}
