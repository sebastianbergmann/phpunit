<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

use function file_exists;
use function spl_autoload_register;
use function str_starts_with;
use function strrpos;
use function substr;

require __DIR__ . '/DriverThatReportsWhatHasBeenLoaded.php';

spl_autoload_register(
    static function (string $className): void
    {
        if (!str_starts_with($className, __NAMESPACE__ . '\\')) {
            return;
        }

        $file = __DIR__ . '/lazily-loaded-source/' . substr($className, strrpos($className, '\\') + 1) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    },
);
