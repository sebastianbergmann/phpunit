<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CodeCoverageDriver;

use const PHP_EOL;
use function array_keys;
use function basename;
use function printf;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\CodeCoverage;

final class PrintCollectedCodeCoverageSubscriber implements ExecutionFinishedSubscriber
{
    public function notify(ExecutionFinished $event): void
    {
        $codeCoverage = CodeCoverage::instance();

        if (!$codeCoverage->isActive()) {
            print 'code coverage is not being collected' . PHP_EOL;

            return;
        }

        foreach (array_keys($codeCoverage->codeCoverage()->getData()->lineCoverage()) as $file) {
            printf(
                'code coverage was collected for %s%s',
                basename($file),
                PHP_EOL,
            );
        }
    }
}
