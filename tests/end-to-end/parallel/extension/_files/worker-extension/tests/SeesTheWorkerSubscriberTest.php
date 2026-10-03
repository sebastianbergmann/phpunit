<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerExtension\Worker;

use function getenv;
use PHPUnit\Framework\TestCase;

final class SeesTheWorkerSubscriberTest extends TestCase
{
    public function testTheSubscriberRegisteredInTheWorkerSawThisTestBeingPrepared(): void
    {
        $this->assertNotFalse(getenv('PHPUNIT_WORKER_ID'));
        $this->assertSame(1, Probe::$preparationsSeen);
    }

    public function testTheSubscriberPersistsAcrossTheTestsOfAUnit(): void
    {
        $this->assertSame(2, Probe::$preparationsSeen);
    }
}
