<?php
namespace Pta\Core;

defined('ABSPATH') || exit;

final class Bootstrap
{
    public static function init(): void
    {
        update_option('pta_core_bootstrap_loaded_at', time());
    }
}
