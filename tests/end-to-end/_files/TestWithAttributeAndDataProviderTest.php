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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class TestWithAttributeAndDataProviderTest extends TestCase
{
    public static function provider(): iterable
    {
        yield 'foo' => ['bar', 'baz'];
    }

    #[TestWith(['a', 'b'], 'foo')]
    #[DataProvider('provider')]
    public function testWithDifferentProviderTypes($one, $two): void
    {
        $this->assertTrue(true);
    }
}
