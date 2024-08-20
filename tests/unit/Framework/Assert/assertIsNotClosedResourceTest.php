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

use function fclose;
use function fopen;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversMethod(Assert::class, 'assertIsNotClosedResource')]
#[TestDox('assertIsNotClosedResource()')]
#[Small]
final class assertIsNotClosedResourceTest extends TestCase
{
    #[DataProviderExternal(assertIsClosedResourceTest::class, 'failureProvider')]
    public function testSucceedsWhenConstraintEvaluatesToTrue(mixed $actual): void
    {
        $this->assertIsNotClosedResource($actual);
    }

    public function testFailsWhenConstraintEvaluatesToFalse(): void
    {
        $closedResource = fopen(__FILE__, 'r');
        fclose($closedResource);

        $this->expectException(AssertionFailedError::class);

        $this->assertIsNotClosedResource($closedResource);
    }
}
