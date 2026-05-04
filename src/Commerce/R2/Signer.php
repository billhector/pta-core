<?php
namespace Pta\Core\Commerce\R2;

defined('ABSPATH') || exit;

final class Signer
{
    public function __construct(
        private string $access_key,
        private string $secret_key,
        private string $endpoint,
        private string $bucket,
        private string $region = 'auto'
    ) {}

    public function presign_get(string $object_key, int $expires_seconds = 600, ?int $now_unix = null): string
    {
        return $this->presign($object_key, 'GET', $expires_seconds, $now_unix);
    }

    public function presign_put(string $object_key, int $expires_seconds = 600, ?int $now_unix = null): string
    {
        return $this->presign($object_key, 'PUT', $expires_seconds, $now_unix);
    }

    private function presign(string $object_key, string $method, int $expires_seconds, ?int $now_unix): string
    {
        $now       = $now_unix ?? time();
        $amz_date  = gmdate('Ymd\THis\Z', $now);
        $datestamp = gmdate('Ymd', $now);
        $service   = 's3';
        $host      = parse_url($this->endpoint, PHP_URL_HOST);

        $encoded_key   = $this->uri_encode_path($object_key);
        $canonical_uri = '/' . $this->bucket . '/' . $encoded_key;

        $credential_scope = sprintf('%s/%s/%s/aws4_request', $datestamp, $this->region, $service);
        $credential       = sprintf('%s/%s', $this->access_key, $credential_scope);

        $query = [
            'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential'    => $credential,
            'X-Amz-Date'          => $amz_date,
            'X-Amz-Expires'       => (string) $expires_seconds,
            'X-Amz-SignedHeaders' => 'host',
        ];
        ksort($query);
        $canonical_querystring = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        $canonical_headers = "host:{$host}\n";
        $signed_headers    = 'host';
        $payload_hash      = 'UNSIGNED-PAYLOAD';

        $canonical_request = implode("\n", [
            $method,
            $canonical_uri,
            $canonical_querystring,
            $canonical_headers,
            $signed_headers,
            $payload_hash,
        ]);

        $string_to_sign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $amz_date,
            $credential_scope,
            hash('sha256', $canonical_request),
        ]);

        $k_date    = hash_hmac('sha256', $datestamp, 'AWS4' . $this->secret_key, true);
        $k_region  = hash_hmac('sha256', $this->region, $k_date, true);
        $k_service = hash_hmac('sha256', $service, $k_region, true);
        $k_signing = hash_hmac('sha256', 'aws4_request', $k_service, true);
        $signature = hash_hmac('sha256', $string_to_sign, $k_signing);

        return sprintf(
            '%s%s?%s&X-Amz-Signature=%s',
            rtrim($this->endpoint, '/'),
            $canonical_uri,
            $canonical_querystring,
            $signature
        );
    }

    private function uri_encode_path(string $path): string
    {
        return implode('/', array_map(
            static fn($seg) => rawurlencode($seg),
            explode('/', $path)
        ));
    }

    public static function from_constants(): self
    {
        return new self(
            PTA_R2_ACCESS_KEY,
            PTA_R2_SECRET_KEY,
            PTA_R2_ENDPOINT,
            PTA_R2_BUCKET
        );
    }
}
