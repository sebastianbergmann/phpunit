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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertFileNotEqualsFileIgnoringWhitespace')]
#[CoversMethod(Assert::class, 'stringEqualsStringIgnoringWhitespace')]
#[TestDox('assertFileNotEqualsFileIgnoringWhitespace()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertFileNotEqualsFileIgnoringWhitespaceTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertFileNotEqualsFileIgnoringWhitespace(
            TEST_FILES_PATH . 'string_with_spaces.txt',
            TEST_FILES_PATH . 'string_different_content.txt',
        );
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertFileNotEqualsFileIgnoringWhitespace(
            TEST_FILES_PATH . 'string_with_spaces.txt',
            TEST_FILES_PATH . 'string_with_tabs.txt',
        );
    }
}
