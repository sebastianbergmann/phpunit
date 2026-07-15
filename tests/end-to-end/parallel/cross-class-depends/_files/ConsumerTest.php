<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelCrossClassDepends;

use function getenv;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\TestCase;

final class ConsumerTest extends TestCase
{
    #[DependsExternal(ProducerTest::class, 'testProduces')]
    public function testConsumes(string $value): void
    {
        // The return value of the test this test depends on was produced by
        // another test class — in a parallel run, by another worker process.
        $this->assertSame('produced value', $value);

        // A test with a cross-class dependency runs in the main process at
        // its suite index, where the results of every test that precedes it
        // in suite order have been imported — never in a worker.
        $this->assertFalse(getenv('PHPUNIT_WORKER_ID'));
    }
}
