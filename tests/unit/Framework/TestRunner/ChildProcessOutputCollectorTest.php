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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase\OutputBuffer;
use PHPUnit\Framework\TestRunner\ChildProcessOutputCollector;
use PHPUnit\TestFixture\Success;
use ReflectionProperty;

#[CoversClass(ChildProcessOutputCollector::class)]
#[Small]
final class ChildProcessOutputCollectorTest extends TestCase
{
    #[TestDox('Returns empty string when test expects output and STDOUT is not rewindable')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testReturnsEmptyStringWhenTestExpectsOutput(): void
    {
        $test = new Success('testOne');

        $outputBuffer = new ReflectionProperty(TestCase::class, 'outputBuffer')->getValue($test);
        new ReflectionProperty(OutputBuffer::class, 'expectedString')->setValue($outputBuffer, 'whatever');

        $this->assertSame('', ChildProcessOutputCollector::collect($test));
    }

    #[TestDox('Returns test output when test does not expect output and STDOUT is not rewindable')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testReturnsTestOutputWhenTestDoesNotExpectOutput(): void
    {
        $test = new Success('testOne');

        $outputBuffer = new ReflectionProperty(TestCase::class, 'outputBuffer')->getValue($test);
        new ReflectionProperty(OutputBuffer::class, 'output')->setValue($outputBuffer, 'expected test output');

        $this->assertSame('expected test output', ChildProcessOutputCollector::collect($test));
    }
}
