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

use function fclose;
use function fopen;
use function is_resource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClosedResourceDataTest extends TestCase
{
    public static function closedResourceProvider(): array
    {
        $resource = fopen('php://memory', 'r');

        fclose($resource);

        return [
            [$resource],
        ];
    }

    #[DataProvider('closedResourceProvider')]
    public function testReceivesAClosedResource(mixed $value): void
    {
        $this->assertFalse(is_resource($value));
    }
}
