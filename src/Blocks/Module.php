<?php
namespace Pta\Core\Blocks;

defined('ABSPATH') || exit;

final class Module
{
    private const BLOCKS = [
        'county-contacts',
        'municipality-dropdown',
        'conditional-municipality-lookup',
        'county-purchase',
        'related-guide-posts',
        'buy-button',
    ];

    public function register(): void
    {
        add_action('init', [$this, 'register_blocks']);
    }

    public function register_blocks(): void
    {
        foreach (self::BLOCKS as $name) {
            $dir = PTA_CORE_DIR . 'blocks/' . $name;
            if (file_exists($dir . '/block.json')) {
                register_block_type_from_metadata($dir);
            }
        }
    }
}
