<?php
namespace Pta\Core\Analytics;

use Pta\Core\Commerce\OrderStore;

defined('ABSPATH') || exit;

final class Module
{
    public function register(): void
    {
        add_action('pta_core_order_paid', [$this, 'on_order_paid'], 30, 3);
    }

    public function on_order_paid(int $order_id, string $email, string $r2_key): void
    {
        $order = OrderStore::find_by_id($order_id);
        if (!$order) return;

        (new Ga4MeasurementProtocol())->track_purchase(
            $order_id,
            $email,
            (int) $order['amount_total'],
            (string) $order['currency'],
            (string) $order['stripe_product_id']
        );
    }
}
