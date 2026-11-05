<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelInProcessUnitExclusivity;

use function file_put_contents;
use function getenv;
use function microtime;
use function usleep;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class IsolatedTest extends TestCase
{
    public function testThatRecordsWhenItRan(): void
    {
        $start = microtime(true);

        usleep(200000);

        $intervals = getenv('PHPUNIT_TEST_INTERVALS');

        $this->assertIsString($intervals);

        file_put_contents($intervals . '.isolated', $start . ' ' . microtime(true));
    }
}
