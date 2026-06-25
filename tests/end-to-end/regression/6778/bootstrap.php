<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
\spl_autoload_register(static function (string $class): void
{
    if ($class === 'PHPUnit\TestFixture\Issue6778\DeprecatedClass') {
        require __DIR__ . '/src/DeprecatedClass.php';
    }
});
