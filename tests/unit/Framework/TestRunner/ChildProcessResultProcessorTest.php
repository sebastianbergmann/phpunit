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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
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
            ->method('testErrored');

        $this->processor($emitter)->process(new Success('testOne'), '', 'error');
    }

    #[TestDox('Emits Test\Errored event when process result cannot be unserialized')]
    public function testEmitsErrorEventWhenProcessResultCannotBeUnserialized(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $this->processor($emitter)->process(new Success('testOne'), '', '');
    }

    private function processor(Emitter $emitter): ChildProcessResultProcessor
    {
        return new ChildProcessResultProcessor(
            new Facade,
            $emitter,
            new PassedTests,
            new CodeCoverage,
        );
    }
}
