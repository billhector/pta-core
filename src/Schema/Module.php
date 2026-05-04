<?php
namespace Pta\Core\Schema;

defined('ABSPATH') || exit;

final class Module
{
    public function register(): void
    {
        add_action('wp_footer', static function () {
            (new ProductSchema())->maybe_render();
        });
    }
}
