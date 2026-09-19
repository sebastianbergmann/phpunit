<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestWithDifferentNames;

#[CoversClass(DeprecationExpectation::class)]
#[Small]
final class DeprecationExpectationTest extends TestCase
{
    public function testVerifyDoesNothingWhenNoDeprecationIsExpected(): void
    {
        $test = new TestWithDifferentNames('testWithName');

        (new DeprecationExpectation)->verify($test);

        $this->assertSame(0, $test->numberOfAssertionsPerformed());
    }

    public function testFailsWhenExpectedMessageWasNotTriggered(): void
    {
        $test        = new TestWithDifferentNames('testWithName');
        $expectation = new DeprecationExpectation;

        $expectation->expectMessage('this deprecation is not triggered');

        try {
            $expectation->verify($test);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Expected deprecation with message "this deprecation is not triggered" was not triggered', $e->getMessage());
            $this->assertSame(1, $test->numberOfAssertionsPerformed());

            return;
        }

        $this->fail();
    }

    public function testFailsWhenNoTriggeredMessageMatchesExpectedRegularExpression(): void
    {
        $test        = new TestWithDifferentNames('testWithName');
        $expectation = new DeprecationExpectation;

        $expectation->expectMessageMatches('/this deprecation is not triggered/');

        try {
            $expectation->verify($test);
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Expected deprecation with message matching regular expression "/this deprecation is not triggered/" was not triggered', $e->getMessage());
            $this->assertSame(1, $test->numberOfAssertionsPerformed());

            return;
        }

        $this->fail();
    }

    #[IgnoreDeprecations]
    public function testPassesWhenExpectedMessageWasTriggered(): void
    {
        $test        = new TestWithDifferentNames('testWithName');
        $expectation = new DeprecationExpectation;

        $expectation->expectMessage('this deprecation is triggered');

        trigger_error('this deprecation is triggered', E_USER_DEPRECATED);

        $expectation->verify($test);

        $this->assertSame(1, $test->numberOfAssertionsPerformed());
    }

    #[IgnoreDeprecations]
    public function testPassesWhenTriggeredMessageMatchesExpectedRegularExpression(): void
    {
        $test        = new TestWithDifferentNames('testWithName');
        $expectation = new DeprecationExpectation;

        $expectation->expectMessageMatches('/deprecation is triggered$/');

        trigger_error('this deprecation is triggered', E_USER_DEPRECATED);

        $expectation->verify($test);

        $this->assertSame(1, $test->numberOfAssertionsPerformed());
    }
}
