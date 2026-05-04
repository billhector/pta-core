<?php
namespace Pta\Core\Mail;

defined('ABSPATH') || exit;

final class Client
{
    private const BASE = 'https://api.mailjet.com';

    public function __construct(
        private string $api_key,
        private string $secret_key
    ) {}

    public static function from_constants(): self
    {
        return new self(PTA_MAILJET_API_KEY, PTA_MAILJET_SECRET_KEY);
    }

    public function auth_header(): string
    {
        return 'Basic ' . base64_encode($this->api_key . ':' . $this->secret_key);
    }

    public function endpoint(string $path, string $version = '3'): string
    {
        return self::BASE . '/v' . $version . $path;
    }

    public function post(string $path, array $body, string $version = '3'): array
    {
        $response = wp_remote_post($this->endpoint($path, $version), [
            'headers' => [
                'Authorization' => $this->auth_header(),
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode($body),
            'timeout' => 15,
        ]);

        return $this->unpack($response);
    }

    public function get(string $path, array $query = [], string $version = '3'): array
    {
        $url = $this->endpoint($path, $version);
        if ($query) {
            $url .= '?' . http_build_query($query);
        }
        $response = wp_remote_get($url, [
            'headers' => ['Authorization' => $this->auth_header()],
            'timeout' => 15,
        ]);
        return $this->unpack($response);
    }

    private function unpack($response): array
    {
        if (is_wp_error($response)) {
            throw new \RuntimeException('Mailjet HTTP error: ' . $response->get_error_message());
        }
        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($code >= 400) {
            $msg = $data['ErrorMessage'] ?? ($data['Messages'][0]['Errors'][0]['ErrorMessage'] ?? 'unknown');
            throw new \RuntimeException("Mailjet API {$code}: {$msg}");
        }
        return is_array($data) ? $data : [];
    }
}
