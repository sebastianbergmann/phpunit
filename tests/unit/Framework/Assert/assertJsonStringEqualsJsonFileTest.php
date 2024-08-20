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

use function json_encode;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertJsonStringEqualsJsonFile')]
#[TestDox('assertJsonStringEqualsJsonFile()')]
#[Small]
final class assertJsonStringEqualsJsonFileTest extends TestCase
{
    public function testSucceedsWhenConstraintEvaluatesToTrue(): void
    {
        $this->assertJsonStringEqualsJsonFile(
            TEST_FILES_PATH . 'JsonData/simpleObject.json',
            json_encode(['Mascott' => 'Tux']),
        );
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertJsonStringEqualsJsonFile(
            TEST_FILES_PATH . 'JsonData/arrayObject.json',
            json_encode(['Mascott' => 'Tux']),
        );
    }
}
