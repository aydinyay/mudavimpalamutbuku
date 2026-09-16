<?php
/**
 * Mudavim v4 — Lightweight PSR-4 Autoloader
 * Composer gerektirmez, cPanel/shared hosting uyumlu.
 * Namespace: Mudavim\Core\ClassName → core/ClassName.php
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'Mudavim\\Core\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;

    $file = __DIR__ . '/core/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require_once $file;
});
