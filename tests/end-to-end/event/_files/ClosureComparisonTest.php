<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\TestCase;

final class ClosureComparisonTest extends TestCase
{
    public function testClosureComparison(): void
    {
        $factory = static fn (): callable => static function (): int
        {
            return 1;
        };

        $this->assertEquals($factory(), $factory());
    }
}
