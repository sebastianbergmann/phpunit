<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
\spl_autoload_register(static function (string $className): void
{
    $prefix = 'PHPUnit\\TestFixture\\TestImpactData\\ProcessOfItsOwn\\';

    if (!\str_starts_with($className, $prefix)) {
        return;
    }

    $file = __DIR__ . '/src/' . \substr($className, \strlen($prefix)) . '.php';

    if (\is_file($file)) {
        require $file;
    }
});
