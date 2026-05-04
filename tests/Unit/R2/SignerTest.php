<?php
namespace Pta\Core\Tests\R2;

use PHPUnit\Framework\TestCase;
use Pta\Core\Commerce\R2\Signer;

final class SignerTest extends TestCase
{
    public function test_presigned_url_contains_required_query_params(): void
    {
        $signer = new Signer(
            access_key: 'TEST_ACCESS',
            secret_key: 'TEST_SECRET',
            endpoint:   'https://abc123.r2.cloudflarestorage.com',
            bucket:     'pta-downloads',
            region:     'auto'
        );

        $url = $signer->presign_get('guides/illinois/cook.zip', 600, 1700000000);

        $this->assertStringContainsString('X-Amz-Algorithm=AWS4-HMAC-SHA256', $url);
        $this->assertStringContainsString('X-Amz-Credential=', $url);
        $this->assertStringContainsString('X-Amz-Date=', $url);
        $this->assertStringContainsString('X-Amz-Expires=600', $url);
        $this->assertStringContainsString('X-Amz-SignedHeaders=host', $url);
        $this->assertStringContainsString('X-Amz-Signature=', $url);
    }

    public function test_signature_is_deterministic_for_fixed_inputs(): void
    {
        $signer = new Signer('AK', 'SK', 'https://abc.r2.cloudflarestorage.com', 'b', 'auto');
        $a = $signer->presign_get('k.zip', 600, 1700000000);
        $b = $signer->presign_get('k.zip', 600, 1700000000);
        $this->assertSame($a, $b);
    }

    public function test_object_key_with_special_chars_is_url_encoded(): void
    {
        $signer = new Signer('AK', 'SK', 'https://abc.r2.cloudflarestorage.com', 'b', 'auto');
        $url = $signer->presign_get('guides/illinois/cook county.zip', 600, 1700000000);
        $this->assertStringContainsString('cook%20county.zip', $url);
        $this->assertStringNotContainsString('cook county.zip', $url);
    }
}
