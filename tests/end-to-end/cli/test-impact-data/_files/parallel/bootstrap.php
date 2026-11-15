<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\TestFixture\TestImpactData\Parallel\WorkerSetup;

\spl_autoload_register(static function (string $className): void
{
    $prefix = 'PHPUnit\\TestFixture\\TestImpactData\\Parallel\\';

    if (!\str_starts_with($className, $prefix)) {
        return;
    }

    $file = __DIR__ . '/src/' . \substr($className, \strlen($prefix)) . '.php';

    if (\is_file($file)) {
        require $file;
    }
});

/*
 * What is set up here is only set up in a worker process of a parallel test
 * run, a database of its own for instance: the main process does not execute
 * the code that sets it up.
 */
if (\getenv('PHPUNIT_WORKER_ID') !== false) {
    WorkerSetup::prepare();
}
