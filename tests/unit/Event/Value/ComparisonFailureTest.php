<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComparisonFailure::class)]
#[Small]
final class ComparisonFailureTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $expected = 'expected';
        $actual   = 'actual';
        $diff     = 'diff';

        $comparisonFailure = new ComparisonFailure($expected, $actual, $diff);

        $this->assertSame($expected, $comparisonFailure->expected());
        $this->assertSame($actual, $comparisonFailure->actual());
        $this->assertSame($diff, $comparisonFailure->diff());
    }
}
