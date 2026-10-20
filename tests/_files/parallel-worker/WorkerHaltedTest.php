<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorker;

use function usleep;
use PHPUnit\Framework\TestCase;

/**
 * A test class whose first test finishes right away while its second one is
 * still running when the parent asks the worker to halt the unit, so that the
 * tests of a halted worker can check that the running test finishes, that the
 * third test is not started, and that tearDownAfterClass() is run.
 */
final class WorkerHaltedTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
    }

    public function testThatFinishesRightAway(): void
    {
        $this->assertTrue(true);
    }

    public function testThatIsRunningWhenTheHaltIsRequested(): void
    {
        usleep(500000);

        $this->assertTrue(true);
    }

    public function testThatIsNotStartedOnceTheUnitHalts(): void
    {
        $this->assertTrue(true);
    }
}
