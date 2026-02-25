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

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class TestWithDuplicateNamedKeyTest extends TestCase
{
    #[TestWith([1], 'same')]
    #[TestWith([2], 'same')]
    public function testOne(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }
}
