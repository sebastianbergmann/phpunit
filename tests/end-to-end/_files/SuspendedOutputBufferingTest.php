<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;

final class SuspendedOutputBufferingTest extends TestCase
{
    protected function setUp(): void
    {
        print 'setUp ';
    }

    public function testOutputExpectationIncludesOutputPrintedBeforeOutputBufferingWasSuspended(): void
    {
        $this->expectOutputString('setUp test');

        print 'test';
    }

    protected function invokeTestMethod(string $methodName, array $testArguments): mixed
    {
        $this->suspendOutputBuffering();
        $this->resumeOutputBuffering();

        return parent::invokeTestMethod($methodName, $testArguments);
    }
}
