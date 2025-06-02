<?php declare(strict_types=1);
require __DIR__ . '/trigger_deprecation.php';

spl_autoload_register(function ($class) {
    $parts = explode('\\', $class);
    $file = end($parts) . '.php';

    match ($file) {
        'FirstPartyClass.php' => require __DIR__ . '/../src/' . $file,
        'ThirdPartyClass.php' => require __DIR__ . '/' . $file,
        default => throw new LogicException
    };
});

