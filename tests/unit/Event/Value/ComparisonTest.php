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

#[CoversClass(Comparison::class)]
#[Small]
final class ComparisonTest extends TestCase
{
    public function testHasExpectedValueAsString(): void
    {
        $this->assertSame('expected-value', $this->comparison()->expected());
    }

    public function testHasActualValueAsString(): void
    {
        $this->assertSame('actual-value', $this->comparison()->actual());
    }

    private function comparison(): Comparison
    {
        return new Comparison('expected-value', 'actual-value');
    }
}
