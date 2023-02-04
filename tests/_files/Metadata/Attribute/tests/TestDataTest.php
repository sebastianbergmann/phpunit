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

use PHPUnit\Framework\Attributes\TestData;
use PHPUnit\Framework\TestCase;

final class TestDataTest extends TestCase
{
    #[TestData(1, 2, 3)]
    public function testOne(): void
    {
    }

    #[TestData(1, 2, 3, name: 'Name1')]
    public function testOneWithName(): void
    {
    }
}
