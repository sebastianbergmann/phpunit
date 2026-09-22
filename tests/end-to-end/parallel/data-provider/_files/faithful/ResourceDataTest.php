<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelDataProvider;

use function fopen;
use function fread;
use function fwrite;
use function getenv;
use function rewind;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceDataTest extends TestCase
{
    public static function resourceProvider(): array
    {
        $resource = fopen('php://memory', 'w+');

        fwrite($resource, 'written by the data provider');
        rewind($resource);

        return [
            [$resource],
        ];
    }

    #[DataProvider('resourceProvider')]
    public function testReceivesAnOpenResource(mixed $resource): void
    {
        $this->assertIsResource($resource);
        $this->assertSame('written by the data provider', fread($resource, 64));
        $this->assertNotFalse(getenv('PHPUNIT_WORKER_ID'));
    }
}
