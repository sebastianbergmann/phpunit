<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DataProvider;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValueIsNotArrayStringKeyTest extends TestCase
{
    public static function values(): Generator
    {
        yield 'mykey' => 42;
    }

    #[DataProvider('values')]
    public function testOne($value): void
    {
        $this->assertTrue(true);
    }
}
