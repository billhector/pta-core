<?php
namespace Pta\Core\Schema;

defined('ABSPATH') || exit;

final class ProductSchema
{
    private const ELIGIBLE_POST_TYPES = ['county-guide', 'download'];

    public function maybe_render(): void
    {
        if (!is_singular(self::ELIGIBLE_POST_TYPES)) {
            return;
        }
        echo "<script type=\"application/ld+json\">\n";
        echo wp_json_encode($this->build_payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "\n</script>\n";
    }

    public function build_payload(): array
    {
        $county = (string) (function_exists('rwmb_meta') ? rwmb_meta('county_name') : '');
        $state  = (string) (function_exists('rwmb_meta') ? rwmb_meta('state_name') : '');
        $price  = (string) (function_exists('rwmb_meta') ? rwmb_meta('guide_price') : '');

        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => trim("{$county} County Property Tax Appeal Guide"),
            'description' => trim("Complete property tax appeal guide for {$county} County, {$state}. Step-by-step instructions, forms, and deadlines included."),
            'brand'       => ['@type' => 'Brand', 'name' => 'PropertyTaxAppealGuides.com'],
            'category'    => 'Tax Appeal Guides',
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => $price,
                'priceCurrency' => 'USD',
                'availability'  => 'https://schema.org/InStock',
                'url'           => get_permalink() ?: home_url('/'),
                'seller'        => ['@type' => 'Organization', 'name' => 'PropertyTaxAppealGuides.com'],
            ],
        ];
    }
}
