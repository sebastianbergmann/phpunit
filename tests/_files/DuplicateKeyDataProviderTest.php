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

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DuplicateKeyDataProviderTest extends TestCase
{
    public static function dataProvider(): Generator
    {
        yield 'foo' => ['foo'];

        yield 'foo' => ['bar'];
    }

    #[DataProvider('dataProvider')]
    public function test($arg): void
    {
    }
}
