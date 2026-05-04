<?php
namespace Pta\Core\Mail;

defined('ABSPATH') || exit;

final class Module
{
    public function register(): void
    {
        add_action('phpmailer_init', static function ($mailer) {
            (new Smtp())->configure($mailer);
        });

        add_action('pta_core_order_refunded', static function (string $payment_intent) {
            (new \Pta\Core\Commerce\Email\RefundConfirmation())->send($payment_intent);
        });

        add_action('pta_core_order_paid', static function (int $order_id, string $email, string $r2_key) {
            try {
                (new Contacts(Client::from_constants()))->add_to_list($email);
            } catch (\Throwable $e) {
                error_log('pta-core contact sync failed: ' . $e->getMessage());
            }
        }, 20, 3);
    }
}
