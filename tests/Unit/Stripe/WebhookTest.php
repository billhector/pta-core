<?php
namespace Pta\Core\Tests\Stripe;

use PHPUnit\Framework\TestCase;
use Pta\Core\Commerce\Stripe\Webhook;

final class WebhookTest extends TestCase
{
    public function test_valid_signature_passes(): void
    {
        $payload = '{"id":"evt_test","type":"ping"}';
        $secret  = 'whsec_test_xyz';
        $ts      = time();
        $sig     = hash_hmac('sha256', "{$ts}.{$payload}", $secret);
        $header  = "t={$ts},v1={$sig}";

        $w = new Webhook();
        $this->assertTrue($w->verify_signature($payload, $header, $secret));
    }

    public function test_tampered_body_fails(): void
    {
        $payload = '{"id":"evt_test","type":"ping"}';
        $secret  = 'whsec_test_xyz';
        $ts      = time();
        $sig     = hash_hmac('sha256', "{$ts}.{$payload}", $secret);
        $header  = "t={$ts},v1={$sig}";

        $w = new Webhook();
        $this->assertFalse($w->verify_signature($payload . 'X', $header, $secret));
    }

    public function test_old_timestamp_rejected(): void
    {
        $payload = '{}';
        $secret  = 'whsec_test_xyz';
        $ts      = time() - 600;
        $sig     = hash_hmac('sha256', "{$ts}.{$payload}", $secret);
        $header  = "t={$ts},v1={$sig}";

        $w = new Webhook();
        $this->assertFalse($w->verify_signature($payload, $header, $secret));
    }

    public function test_empty_secret_or_header_rejected(): void
    {
        $w = new Webhook();
        $this->assertFalse($w->verify_signature('{}', '', 'whsec'));
        $this->assertFalse($w->verify_signature('{}', 't=1,v1=x', ''));
    }
}
