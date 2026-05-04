<?php
namespace Pta\Core\Tests\MagicLink;

use PHPUnit\Framework\TestCase;
use Pta\Core\Commerce\MagicLink\Token;

final class TokenTest extends TestCase
{
    public function test_mint_then_verify_roundtrip(): void
    {
        $token = Token::mint(123, 'a@b.com');
        $payload = Token::verify($token);
        $this->assertSame(123, $payload['order_id']);
        $this->assertSame('a@b.com', $payload['email']);
        $this->assertSame(1, $payload['v']);
    }

    public function test_tampered_payload_fails_verification(): void
    {
        $token = Token::mint(123, 'a@b.com');
        $parts = explode('_', $token);
        // Toggle first char of payload portion to guarantee a change.
        $first = $parts[1][0];
        $parts[1][0] = ($first === 'A') ? 'B' : 'A';
        $tampered = implode('_', $parts);
        $this->assertNotSame($token, $tampered, 'tamper must mutate the token');
        $this->assertNull(Token::verify($tampered));
    }

    public function test_tampered_signature_fails_verification(): void
    {
        $token = Token::mint(123, 'a@b.com');
        $this->assertNull(Token::verify($token . 'X'));
    }

    public function test_malformed_token_returns_null(): void
    {
        $this->assertNull(Token::verify('not_a_token'));
        $this->assertNull(Token::verify(''));
        $this->assertNull(Token::verify('pta_only_two_parts'));
    }

    public function test_mint_is_deterministic_for_same_inputs(): void
    {
        $a = Token::mint(1, 'a@b.com');
        $b = Token::mint(1, 'a@b.com');
        $this->assertSame($a, $b);
        $this->assertNotNull(Token::verify($a));
    }
}
