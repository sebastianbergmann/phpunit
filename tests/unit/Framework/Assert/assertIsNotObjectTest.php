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
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use stdClass;

#[CoversMethod(Assert::class, 'assertIsNotObject')]
#[TestDox('assertIsNotObject()')]
#[Small]
final class assertIsNotObjectTest extends TestCase
{
    #[DataProviderExternal(assertIsObjectTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $actual): void
    {
        $this->assertIsNotObject($actual);
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $this->expectException(AssertionFailedError::class);

        $this->assertIsNotObject(new stdClass);
    }
}
