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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class TestDataProviderExternalAndDataProviderTest extends TestCase
{
    public static function externalProvider(): iterable
    {
        yield 'foo' => ['bar', 'baz'];
    }

    public static function provider(): iterable
    {
        yield 'foo2' => ['bar', 'baz'];
    }

    #[DataProvider('provider')]
    #[DataProviderExternal(self::class, 'externalProvider')]
    public function testWithDifferentProviderTypes($one, $two): void
    {
        $this->assertTrue(true);
    }
}
