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

#[CoversMethod(Assert::class, 'assertFileEqualsCanonicalizing')]
#[TestDox('assertFileEqualsCanonicalizing()')]
#[Small]
final class assertFileEqualsCanonicalizingTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertFileNotEquals(TEST_FILES_PATH . 'foo.txt', TEST_FILES_PATH . 'bar.txt');
        $this->assertFileEqualsCanonicalizing(TEST_FILES_PATH . 'foo.txt', TEST_FILES_PATH . 'foo.txt');
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertFileEqualsCanonicalizing(TEST_FILES_PATH . 'foo.txt', TEST_FILES_PATH . 'foo.xml');
    }
}
