<?php
namespace Pta\Core\Commerce;

defined('ABSPATH') || exit;

final class Module
{
    public function register(): void
    {
        add_action('rest_api_init',     [$this, 'register_rest_routes']);
        add_action('init',              [$this, 'register_rewrites']);
        add_filter('query_vars',        [$this, 'add_query_vars']);
        add_action('template_redirect', static function () {
            (new \Pta\Core\Commerce\MagicLink\Redownload())->maybe_render();
        });
        add_action('pta_core_order_paid', static function (int $order_id, string $email, string $r2_key) {
            (new \Pta\Core\Commerce\Email\OrderConfirmation())->send($order_id, $email, $r2_key);
        }, 10, 3);
    }

    public function register_rest_routes(): void
    {
        register_rest_route('pta/v1', '/ping', [
            'methods'             => 'GET',
            'callback'            => static fn() => ['ok' => true, 'time' => time()],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('pta/v1', '/checkout', [
            'methods'             => 'POST',
            'callback'            => [$this, 'rest_checkout'],
            'permission_callback' => '__return_true',
            'args' => [
                'price_id' => ['type' => 'string', 'required' => true],
            ],
        ]);

        register_rest_route('pta/v1', '/stripe/webhook', [
            'methods'             => 'POST',
            'callback'            => static function (\WP_REST_Request $req) {
                return (new \Pta\Core\Commerce\Stripe\Webhook())->handle($req);
            },
            'permission_callback' => '__return_true',
        ]);
    }

    public function register_rewrites(): void
    {
        add_rewrite_rule('^redownload/?$', 'index.php?pta_redownload=1', 'top');
    }

    public function add_query_vars(array $vars): array
    {
        $vars[] = 'pta_redownload';
        return $vars;
    }

    public function rest_checkout(\WP_REST_Request $req): \WP_REST_Response
    {
        try {
            $checkout = new \Pta\Core\Commerce\Stripe\Checkout(new \Pta\Core\Commerce\Stripe\Client());
            $session = $checkout->create_session(
                $req->get_param('price_id'),
                home_url('/?pta_thank_you=1'),
                home_url('/?pta_cancelled=1')
            );
            return new \WP_REST_Response(['checkout_url' => $session['url']], 200);
        } catch (\Throwable $e) {
            return new \WP_REST_Response(['error' => $e->getMessage()], 500);
        }
    }
}
