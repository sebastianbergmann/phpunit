<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelSerialization;

use function fopen;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ObjectHoldingAResourceTest extends TestCase
{
    public static function objectProvider(): array
    {
        $object = new stdClass;

        // A reference back to the object itself exercises the cycle guard that
        // keeps the resource walk from recursing forever.
        $object->self     = $object;
        $object->resource = fopen('php://memory', 'r');

        return [
            [$object],
        ];
    }

    #[DataProvider('objectProvider')]
    public function testReceivesAnObjectHoldingAResource(object $value): void
    {
        $this->assertIsResource($value->resource);
    }
}
