<?php
namespace Pta\Core\Tests\Mail;

use PHPUnit\Framework\TestCase;
use Pta\Core\Mail\Client;

final class ClientTest extends TestCase
{
    public function test_basic_auth_header_is_correctly_encoded(): void
    {
        $c = new Client('apikey123', 'secret456');
        $header = $c->auth_header();
        $this->assertStringStartsWith('Basic ', $header);
        $decoded = base64_decode(substr($header, 6));
        $this->assertSame('apikey123:secret456', $decoded);
    }

    public function test_endpoint_resolution_picks_v3_for_contacts_and_v31_for_send(): void
    {
        $c = new Client('a', 'b');
        $this->assertStringEndsWith('/v3/REST/contact', $c->endpoint('/REST/contact'));
        $this->assertStringEndsWith('/v3.1/send', $c->endpoint('/send', '3.1'));
    }
}
