<?php
namespace Pta\Core\Tests\Routes;

use PHPUnit\Framework\TestCase;
use Pta\Core\Routes\CountyResolver;

final class CountyResolverTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['_pta_test_terms'] = [
            ['term_id' => 100, 'slug' => 'illinois', 'taxonomy' => 'state', 'name' => 'Illinois'],
        ];
        $GLOBALS['_pta_test_posts'] = [
            ['ID' => 892, 'post_name' => 'cook-county', 'state_term_ids' => [100]],
        ];
    }

    public function test_resolve_returns_post_id_for_match(): void
    {
        $this->assertSame(892, (new CountyResolver())->resolve('illinois', 'cook-county'));
    }

    public function test_resolve_returns_null_for_missing_state(): void
    {
        $this->assertNull((new CountyResolver())->resolve('mars', 'cook-county'));
    }

    public function test_resolve_returns_null_for_wrong_county(): void
    {
        $this->assertNull((new CountyResolver())->resolve('illinois', 'no-such-county'));
    }

    public function test_canonical_url_returns_state_county_path(): void
    {
        $url = (new CountyResolver())->canonical_url(892);
        $this->assertSame('https://propertytaxappealguides.com/illinois/cook-county/', $url);
    }

    public function test_canonical_url_returns_null_for_post_without_state_term(): void
    {
        $GLOBALS['_pta_test_posts'][0]['state_term_ids'] = [];
        $this->assertNull((new CountyResolver())->canonical_url(892));
    }
}
