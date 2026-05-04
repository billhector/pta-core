<?php
namespace Pta\Core\Commerce;

defined('ABSPATH') || exit;

final class OrderStore
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_PAID     = 'paid';
    public const STATUS_REFUNDED = 'refunded';

    public static function table(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'pta_orders';
    }

    public static function install_schema(): void
    {
        global $wpdb;
        $table = self::table();
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            stripe_session_id VARCHAR(255) NOT NULL,
            stripe_payment_intent VARCHAR(255) DEFAULT NULL,
            customer_email VARCHAR(255) NOT NULL,
            amount_total INT UNSIGNED NOT NULL,
            currency VARCHAR(8) NOT NULL DEFAULT 'usd',
            stripe_product_id VARCHAR(255) NOT NULL,
            r2_key VARCHAR(512) NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            paid_at DATETIME DEFAULT NULL,
            refunded_at DATETIME DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY stripe_session_id (stripe_session_id),
            KEY customer_email (customer_email),
            KEY status (status)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function find_by_session(string $session_id): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . self::table() . " WHERE stripe_session_id = %s LIMIT 1", $session_id),
            ARRAY_A
        );
        return $row ?: null;
    }

    public static function find_by_id(int $id): ?array
    {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . self::table() . " WHERE id = %d LIMIT 1", $id),
            ARRAY_A
        );
        return $row ?: null;
    }

    public static function find_all_by_email(string $email): array
    {
        global $wpdb;
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . self::table() . " WHERE customer_email = %s AND status = %s ORDER BY paid_at DESC",
                $email,
                self::STATUS_PAID
            ),
            ARRAY_A
        ) ?: [];
    }

    public static function insert_paid(array $data): int
    {
        global $wpdb;
        $now = current_time('mysql', true);
        $wpdb->insert(self::table(), [
            'stripe_session_id'     => $data['stripe_session_id'],
            'stripe_payment_intent' => $data['stripe_payment_intent'] ?? null,
            'customer_email'        => $data['customer_email'],
            'amount_total'          => (int) $data['amount_total'],
            'currency'              => $data['currency'] ?? 'usd',
            'stripe_product_id'     => $data['stripe_product_id'],
            'r2_key'                => $data['r2_key'],
            'status'                => self::STATUS_PAID,
            'created_at'            => $now,
            'paid_at'               => $now,
        ]);
        return (int) $wpdb->insert_id;
    }

    public static function mark_refunded(string $payment_intent): int
    {
        global $wpdb;
        $now = current_time('mysql', true);
        return (int) $wpdb->update(
            self::table(),
            ['status' => self::STATUS_REFUNDED, 'refunded_at' => $now],
            ['stripe_payment_intent' => $payment_intent]
        );
    }
}
