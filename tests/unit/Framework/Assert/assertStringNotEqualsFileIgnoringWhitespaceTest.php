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

use function file_get_contents;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertStringNotEqualsFileIgnoringWhitespace')]
#[CoversMethod(Assert::class, 'stringEqualsStringIgnoringWhitespace')]
#[TestDox('assertStringNotEqualsFileIgnoringWhitespace()')]
#[Small]
#[Group('framework')]
#[Group('framework/assertions')]
final class assertStringNotEqualsFileIgnoringWhitespaceTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertStringNotEqualsFileIgnoringWhitespace(
            TEST_FILES_PATH . 'string_with_spaces.txt',
            file_get_contents(TEST_FILES_PATH . 'string_different_content.txt'),
        );
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFileIgnoringWhitespace(
            TEST_FILES_PATH . 'string_with_spaces.txt',
            file_get_contents(TEST_FILES_PATH . 'string_with_tabs.txt'),
        );
    }
}
