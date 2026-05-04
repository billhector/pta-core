<?php
namespace Pta\Core\Tests\Schema;

use PHPUnit\Framework\TestCase;
use Pta\Core\Schema\ProductSchema;

final class ProductSchemaTest extends TestCase
{
    public function test_payload_has_required_fields(): void
    {
        $p = (new ProductSchema())->build_payload();
        $this->assertSame('https://schema.org', $p['@context']);
        $this->assertSame('Product', $p['@type']);
        $this->assertSame('Cook County Property Tax Appeal Guide', $p['name']);
        $this->assertSame('67', $p['offers']['price']);
        $this->assertSame('USD', $p['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $p['offers']['availability']);
    }

    public function test_payload_serializes_to_valid_json(): void
    {
        $json = json_encode((new ProductSchema())->build_payload());
        $this->assertNotFalse($json);
        $decoded = json_decode($json, true);
        $this->assertSame('Product', $decoded['@type']);
    }
}
