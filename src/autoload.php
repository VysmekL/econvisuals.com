<?php
/**
 * Simple PSR-4 Autoloader
 *
 * Automaticky načítá PHP třídy podle namespace
 */

spl_autoload_register(function ($class) {
    // Namespace prefix
    $prefix = 'App\\';

    // Base directory pro namespace
    $baseDir = __DIR__ . '/';

    // Zkontrolovat, zda třída používá náš namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Získat relativní název třídy
    $relativeClass = substr($class, $len);

    // Nahradit namespace separátory lomítky a přidat .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Pokud soubor existuje, načíst ho
    if (file_exists($file)) {
        require $file;
    }
});
