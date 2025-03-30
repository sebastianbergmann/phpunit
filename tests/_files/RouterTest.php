<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use FooBarHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public static function routesProvider(): array
    {
        return [
            '/foo/bar' => [
                '/foo/bar',
                FooBarHandler::class,
                // ...
            ],
        ];
    }

    #[DataProvider('routesProvider')]
    #[TestDox('Routes $url to $handler')]
    public function testRoutesRequest(string $url, string $handler): void
    {
        $this->assertTrue(true);
    }
}
