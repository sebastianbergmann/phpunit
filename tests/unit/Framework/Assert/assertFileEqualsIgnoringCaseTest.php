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

use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertFileEqualsIgnoringCase')]
#[TestDox('assertFileEqualsIgnoringCase()')]
#[Small]
final class assertFileEqualsIgnoringCaseTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertFileEqualsIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'fooUppercase.xml',
        );
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertFileEqualsIgnoringCase(
            TEST_FILES_PATH . 'foo.xml',
            TEST_FILES_PATH . 'bar.xml',
        );
    }
}
