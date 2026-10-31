<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\StateMachine;

use function spl_autoload_register;
use function str_starts_with;
use function strlen;
use function substr;

spl_autoload_register(
    static function (string $className): void
    {
        $prefix = __NAMESPACE__ . '\\';

        if (str_starts_with($className, $prefix)) {
            require __DIR__ . '/' . substr($className, strlen($prefix)) . '.php';
        }
    },
);
