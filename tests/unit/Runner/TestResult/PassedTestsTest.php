<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(PassedTests::class)]
#[Small]
final class PassedTestsTest extends TestCase
{
    public function testForgetsRecordedPassesWhenReset(): void
    {
        $passedTests = new PassedTests;

        $passedTests->testClassPassed(self::class);

        $this->assertTrue($passedTests->hasTestClassPassed(self::class));

        $passedTests->reset();

        $this->assertFalse($passedTests->hasTestClassPassed(self::class));
    }
}
