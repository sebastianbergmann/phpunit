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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertStringNotEqualsFileCanonicalizing')]
#[TestDox('assertStringNotEqualsFileCanonicalizing()')]
#[Small]
final class assertStringNotEqualsFileCanonicalizingTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertStringNotEqualsFileCanonicalizing(
            TEST_FILES_PATH . 'foo.txt',
            file_get_contents(TEST_FILES_PATH . 'foo.txt') . 'BAR',
        );
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertStringNotEqualsFileCanonicalizing(
            TEST_FILES_PATH . 'foo.txt',
            file_get_contents(TEST_FILES_PATH . 'foo.txt'),
        );
    }
}
