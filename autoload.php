<?php
/**
 * Manual PSR-4 autoloader for the Pta\Core namespace.
 * Maps Pta\Core\Foo\Bar → src/Foo/Bar.php
 */

defined('ABSPATH') || exit;

spl_autoload_register(static function ($class) {
    $prefix = 'Pta\\Core\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
