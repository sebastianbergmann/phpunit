<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\TestWithJson;
use PHPUnit\Framework\TestCase;

final class TestWithTest extends TestCase
{
    #[TestWith([1, 2, 3])]
    public function testOne($one, $two, $three): void
    {
        $this->assertTrue(true);
    }

    #[TestWith([1, 2, 3], 'Name1')]
    public function testOneWithName($one, $two = null, $three = null): void
    {
        $this->assertTrue(true);
    }

    #[TestWithJson('[1, 2, 3]')]
    public function testTwo($one, $two, $three): void
    {
        $this->assertTrue(true);
    }

    #[TestWithJson('[1, 2, 3]', 'Name2')]
    public function testTwoWithName($one, $two, $three): void
    {
        $this->assertTrue(true);
    }
}
