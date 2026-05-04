<?php
namespace Pta\Core\Commerce\MagicLink;

defined('ABSPATH') || exit;

final class Token
{
    private const PREFIX  = 'pta_';
    private const VERSION = 1;

    public static function mint(int $order_id, string $email): string
    {
        $payload = ['order_id' => $order_id, 'email' => strtolower($email), 'v' => self::VERSION];
        $payload_b64 = self::b64u(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $sig_b64     = self::b64u(hash_hmac('sha256', $payload_b64, PTA_MAGIC_LINK_SECRET, true));
        return self::PREFIX . $payload_b64 . '_' . $sig_b64;
    }

    public static function verify(string $token): ?array
    {
        if (!str_starts_with($token, self::PREFIX)) {
            return null;
        }
        $body = substr($token, strlen(self::PREFIX));
        $parts = explode('_', $body, 2);
        if (count($parts) !== 2) {
            return null;
        }
        [$payload_b64, $sig_b64] = $parts;
        $expected = self::b64u(hash_hmac('sha256', $payload_b64, PTA_MAGIC_LINK_SECRET, true));
        if (!hash_equals($expected, $sig_b64)) {
            return null;
        }
        $payload = json_decode(self::b64u_decode($payload_b64), true);
        if (!is_array($payload) || !isset($payload['order_id'], $payload['email'], $payload['v'])) {
            return null;
        }
        return $payload;
    }

    private static function b64u(string $bin): string
    {
        return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
    }

    private static function b64u_decode(string $b64u): string
    {
        $b64 = strtr($b64u, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad) {
            $b64 .= str_repeat('=', 4 - $pad);
        }
        return base64_decode($b64) ?: '';
    }
}
