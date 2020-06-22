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

use function array_shift;
use function sprintf;
use PHPUnit\Runner\BaseTestRunner;

final class SkippedTestCaseTest extends TestCase
{
    public function testDefaults(): void
    {
        $testCase = new SkippedTestCase(
            'Foo',
            'testThatBars'
        );

        $this->assertSame('', $testCase->getMessage());
    }

    public function testGetNameReturnsClassAndMethodName(): void
    {
        $className  = 'Foo';
        $methodName = 'testThatBars';

        $testCase = new SkippedTestCase(
            $className,
            $methodName
        );

        $name = sprintf(
            '%s::%s',
            $className,
            $methodName
        );

        $this->assertSame($name, $testCase->getName());
    }

    public function testGetMessageReturnsMessage(): void
    {
        $message = 'Somehow skipped, right?';

        $testCase = new SkippedTestCase(
            'Foo',
            'testThatBars',
            $message
        );

        $this->assertSame($message, $testCase->getMessage());
    }

    public function testRunMarksTestAsSkipped(): void
    {
        $className  = 'Foo';
        $methodName = 'testThatBars';
        $message    = 'Somehow skipped, right?';

        $testCase = new SkippedTestCase(
            $className,
            $methodName,
            $message
        );

        $result = $testCase->run();

        $this->assertSame(BaseTestRunner::STATUS_SKIPPED, $testCase->getStatus());
        $this->assertSame(1, $result->skippedCount());

        $failures = $result->skipped();

        $failure = array_shift($failures);

        $name = sprintf(
            '%s::%s',
            $className,
            $methodName
        );

        $this->assertSame($name, $failure->getTestName());
        $this->assertSame($message, $failure->exceptionMessage());
    }
}
