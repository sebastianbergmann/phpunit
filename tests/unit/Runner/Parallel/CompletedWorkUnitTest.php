<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(CompletedWorkUnit::class)]
#[UsesClass(TestClassWorkUnit::class)]
#[Small]
final class CompletedWorkUnitTest extends TestCase
{
    public function testCarriesTheUnitTheWorkerFinished(): void
    {
        $unit = $this->unit();

        $this->assertSame($unit, CompletedWorkUnit::fromEnvelope($unit, 'nonce-envelope', 'nonce')->unit());
    }

    public function testCarriesTheEnvelopeTheWorkerWroteAndTheNonceThatAuthenticatesIt(): void
    {
        $completed = CompletedWorkUnit::fromEnvelope($this->unit(), 'nonce-envelope', 'nonce');

        $this->assertSame('nonce-envelope', $completed->serializedResult());
        $this->assertSame('nonce', $completed->nonce());
    }

    public function testAUnitThatWasFinishedHasNotCrashed(): void
    {
        $completed = CompletedWorkUnit::fromEnvelope($this->unit(), 'nonce-envelope', 'nonce');

        $this->assertFalse($completed->crashed());
        $this->assertNull($completed->message());
    }

    public function testCarriesTheUnitWhoseWorkerDied(): void
    {
        $unit = $this->unit();

        $this->assertSame($unit, CompletedWorkUnit::fromCrash($unit)->unit());
    }

    public function testAUnitThatCrashedCarriesNeitherAnEnvelopeNorANonce(): void
    {
        $completed = CompletedWorkUnit::fromCrash($this->unit());

        $this->assertTrue($completed->crashed());
        $this->assertSame('', $completed->serializedResult());
        $this->assertNull($completed->nonce());
    }

    public function testAUnitThatCrashedHasNoMessageWhenNoReasonIsKnown(): void
    {
        $this->assertNull(CompletedWorkUnit::fromCrash($this->unit())->message());
    }

    public function testCarriesTheReasonWhyACrashedUnitDidNotRun(): void
    {
        $completed = CompletedWorkUnit::fromCrash($this->unit(), 'the data cannot be serialized');

        $this->assertTrue($completed->crashed());
        $this->assertSame('the data cannot be serialized', $completed->message());
    }

    private function unit(): TestClassWorkUnit
    {
        return new TestClassWorkUnit(
            0,
            WorkerFirstTest::class,
            [new WorkerFirstTest('testStartsTheProcessLocalCounter')],
        );
    }
}
