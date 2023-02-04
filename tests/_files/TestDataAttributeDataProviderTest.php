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

use PHPUnit\Framework\Attributes\TestData;
use PHPUnit\Framework\TestCase;

final class TestDataAttributeDataProviderTest extends TestCase
{
    #[TestData('a', 'b', name: 'foo')]
    #[TestData('c', 'd', name: 'bar')]
    #[TestData('e', 'f')]
    #[TestData('g', 'h')]
    public function testDataAttribute($one, $two): void
    {
    }

    #[TestData('a', 'b', name: 'foo')]
    #[TestData('c', 'd', name: 'foo')]
    public function testDataDuplicateName($one, $two): void
    {
    }
}
