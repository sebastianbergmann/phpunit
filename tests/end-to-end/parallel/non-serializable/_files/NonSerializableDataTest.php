<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelNonSerializable;

use function fopen;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NonSerializableDataTest extends TestCase
{
    public static function resourceProvider(): array
    {
        return [
            [fopen('php://memory', 'r')],
        ];
    }

    public static function closureProvider(): array
    {
        return [
            [static fn (): bool => true],
        ];
    }

    #[DataProvider('resourceProvider')]
    public function testReceivesAnOpenResource(mixed $resource): void
    {
        $this->assertIsResource($resource);
    }

    #[DataProvider('closureProvider')]
    public function testReceivesACallable(mixed $callable): void
    {
        $this->assertIsCallable($callable);
    }
}
