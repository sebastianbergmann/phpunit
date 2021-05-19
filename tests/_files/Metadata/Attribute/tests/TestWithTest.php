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
    public function testOne(): void
    {
    }

    #[TestWithJson('[1, 2, 3]')]
    public function testTwo(): void
    {
    }
}
