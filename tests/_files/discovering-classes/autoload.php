<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
\spl_autoload_register(
    function ($class): void {
        if (strpos($class, 'DiscoveringClasses\\') !== 0) {
            return;
        }

        require_once __DIR__ . substr(str_replace('\\', DIRECTORY_SEPARATOR, $class), 18) . '.php';
    }
);
