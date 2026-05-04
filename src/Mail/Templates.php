<?php
namespace Pta\Core\Mail;

defined('ABSPATH') || exit;

final class Templates
{
    public static function render(string $name, array $vars): string
    {
        $file = __DIR__ . '/templates/' . $name . '.php';
        if (!is_readable($file)) {
            throw new \InvalidArgumentException("template not found: {$name}");
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}
