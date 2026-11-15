<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\Success;
use PHPUnit\TestRunner\TestResult\PassedTests;

#[CoversClass(ChildProcessResultProcessor::class)]
#[Small]
final class ChildProcessResultProcessorTest extends TestCase
{
    #[TestDox('Emits Test\Errored event when standard output is not empty')]
    public function testEmitsErrorEventWhenStderrIsNotEmpty(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testErrored')
            ->seal();

        $this->processor($emitter)->process(new Success('testOne'), '', 'error', 0, null);
    }

    #[TestDox('Emits Test\Errored event when process result cannot be unserialized')]
    public function testEmitsErrorEventWhenProcessResultCannotBeUnserialized(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(new Success('testOne'), '', '', 0, null);
    }

    #[TestDox('Emits Test\Errored event when process result nonce is shorter than the expected nonce')]
    public function testEmitsErrorEventWhenProcessResultIsShorterThanExpectedNonce(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(
            new Success('testOne'),
            'too-short',
            '',
            0,
            null,
            'abcdef0123456789abcdef0123456789',
        );
    }

    #[TestDox('Emits Test\Errored event when process result nonce does not match the expected nonce')]
    public function testEmitsErrorEventWhenProcessResultNonceDoesNotMatchExpectedNonce(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(
            new Success('testOne'),
            '00000000000000000000000000000000payload',
            '',
            0,
            null,
            'abcdef0123456789abcdef0123456789',
        );
    }

    #[TestDox('Emits Test\Errored event when process result contains only the expected nonce and no serialized data')]
    public function testEmitsErrorEventWhenProcessResultContainsOnlyExpectedNonceAndNoSerializedData(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(
            new Success('testOne'),
            'abcdef0123456789abcdef0123456789',
            '',
            0,
            null,
            'abcdef0123456789abcdef0123456789',
        );
    }

    #[TestDox('Emits TestRunner\ChildProcessErrored event with the exit code of the child process')]
    public function testEmitsChildProcessErroredEventWithExitCodeOfChildProcess(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored')
            ->with(
                ChildProcessReason::TestRequiringProcessIsolation,
                'Test was run in child process and ended unexpectedly',
                2,
                null,
            );

        $this->processor($emitter)->process(new Success('testOne'), '', '', 2, null);
    }

    #[TestDox('Emits TestRunner\ChildProcessErrored event with the signal that terminated the child process')]
    public function testEmitsChildProcessErroredEventWithSignalThatTerminatedChildProcess(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored')
            ->with(
                ChildProcessReason::TestRequiringProcessIsolation,
                'Test was run in child process and ended unexpectedly',
                null,
                9,
            );

        $this->processor($emitter)->process(new Success('testOne'), '', '', null, 9);
    }

    private function processor(Emitter $emitter): ChildProcessResultProcessor
    {
        return new ChildProcessResultProcessor(
            new Facade,
            $emitter,
            new PassedTests,
            new CodeCoverage($this->createStub(Emitter::class)),
        );
    }
}
