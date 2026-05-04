<?php
namespace Pta\Core;

defined('ABSPATH') || exit;

final class Bootstrap
{
    public static function init(): void
    {
        update_option('pta_core_bootstrap_loaded_at', time());

        (new \Pta\Core\Commerce\Module())->register();
        (new \Pta\Core\Mail\Module())->register();
        (new \Pta\Core\Schema\Module())->register();
        (new \Pta\Core\Blocks\Module())->register();
        (new \Pta\Core\Hooks\Module())->register();
        (new \Pta\Core\Analytics\Module())->register();
    }
}
