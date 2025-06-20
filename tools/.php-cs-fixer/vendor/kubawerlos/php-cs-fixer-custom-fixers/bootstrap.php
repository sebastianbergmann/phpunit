<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

spl_autoload_register(function (string $class): void {
    if (strncmp($class, 'PhpCsFixerCustomFixers\\', 23) !== 0) {
        return;
    }

    require __DIR__ . '/src/' . str_replace('\\', '/', substr($class, 23)) . '.php';
});
