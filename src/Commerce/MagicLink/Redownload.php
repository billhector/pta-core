<?php
namespace Pta\Core\Commerce\MagicLink;

use Pta\Core\Commerce\OrderStore;
use Pta\Core\Commerce\R2\Signer;

defined('ABSPATH') || exit;

final class Redownload
{
    private const RATE_LIMIT_MAX    = 5;
    private const RATE_LIMIT_WINDOW = 60;

    public function maybe_render(): void
    {
        if (!get_query_var('pta_redownload')) {
            return;
        }

        $token = isset($_GET['token']) ? (string) $_GET['token'] : '';

        if (!$this->rate_limit_ok()) {
            status_header(429);
            $this->render_page('Slow down', '<p>Too many requests. Try again in a minute.</p>');
            exit;
        }

        $payload = Token::verify($token);
        if (!$payload) {
            status_header(400);
            $this->render_page('Invalid link', '<p>That download link is invalid or has been revoked.</p>');
            exit;
        }

        $orders = OrderStore::find_all_by_email($payload['email']);
        if (empty($orders)) {
            status_header(404);
            $this->render_page('No active orders', '<p>No active downloads found for this email.</p>');
            exit;
        }

        $signer = Signer::from_constants();
        $rows = '';
        foreach ($orders as $o) {
            $url = $signer->presign_get($o['r2_key'], 600);
            $rows .= sprintf(
                '<li><a href="%s">Download %s</a> <small>(link valid 10 min)</small></li>',
                esc_url($url),
                esc_html(basename($o['r2_key']))
            );
        }
        $this->render_page('Your downloads', "<ul>{$rows}</ul>");
        exit;
    }

    private function rate_limit_ok(): bool
    {
        $ip  = $this->client_ip();
        $key = 'pta_rl_' . md5($ip);
        $hits = (int) get_transient($key);
        if ($hits >= self::RATE_LIMIT_MAX) {
            return false;
        }
        set_transient($key, $hits + 1, self::RATE_LIMIT_WINDOW);
        return true;
    }

    private function client_ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function render_page(string $title, string $body_html): void
    {
        $title_e = esc_html($title);
        echo "<!doctype html><html><head><meta charset='utf-8'><title>{$title_e}</title>"
           . "<style>body{font-family:system-ui;max-width:640px;margin:4rem auto;padding:0 1rem}</style>"
           . "</head><body><h1>{$title_e}</h1>{$body_html}</body></html>";
    }
}
