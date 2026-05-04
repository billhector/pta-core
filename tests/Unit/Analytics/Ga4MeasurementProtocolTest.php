<?php
namespace Pta\Core\Tests\Analytics;

use PHPUnit\Framework\TestCase;
use Pta\Core\Analytics\Ga4MeasurementProtocol;

final class Ga4MeasurementProtocolTest extends TestCase
{
    public function test_client_id_is_stable_per_email(): void
    {
        $g = new Ga4MeasurementProtocol();
        $a = $g->derive_client_id('a@b.com');
        $b = $g->derive_client_id('a@b.com');
        $this->assertSame($a, $b);
    }

    public function test_client_id_differs_per_email(): void
    {
        $g = new Ga4MeasurementProtocol();
        $this->assertNotSame($g->derive_client_id('a@b.com'), $g->derive_client_id('c@d.com'));
    }

    public function test_email_case_normalized(): void
    {
        $g = new Ga4MeasurementProtocol();
        $this->assertSame($g->derive_client_id('A@B.com'), $g->derive_client_id('a@b.com'));
    }

    public function test_client_id_is_64_hex_chars(): void
    {
        $g = new Ga4MeasurementProtocol();
        $cid = $g->derive_client_id('x@y.com');
        $this->assertSame(64, strlen($cid));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $cid);
    }
}
