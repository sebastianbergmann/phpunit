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
        // The closure created by this factory captures state so that PHP does
        // not reuse a single closure object for all invocations of the factory
        $factory = static fn (int $value): callable => static function () use ($value): int
        {
            return $value;
        };

        $this->assertEquals($factory(1), $factory(1));
    }
}
