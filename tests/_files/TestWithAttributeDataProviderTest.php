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

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class TestWithAttributeDataProviderTest extends TestCase
{
    #[TestWith(['a', 'b'], 'foo')]
    #[TestWith(['c', 'd'], 'bar')]
    #[TestWith(['e', 'f'])]
    #[TestWith(['g', 'h'])]
    public function testWithAttribute($one, $two): void
    {
    }

    #[TestWith(['a', 'b'], 'foo')]
    #[TestWith(['c', 'd'], 'foo')]
    public function testWithDuplicateName($one, $two): void
    {
    }
}
