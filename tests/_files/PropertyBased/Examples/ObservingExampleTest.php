<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Examples;

use const INF;
use const NAN;
use PHPUnit\Framework\TestCase;

final class ObservingExampleTest extends TestCase
{
    public function testObservesValues(int $number): void
    {
        $this->observe('size', $number);
        $this->observe('ratio', 0.5);
        $this->observe('size', 2 * $number);
    }

    public function testObservesAValueWithAnEmptyLabel(int $number): void
    {
        $this->observe('', $number);
    }

    public function testObservesNan(int $number): void
    {
        $this->observe('ratio', NAN);
    }

    public function testObservesInfinity(int $number): void
    {
        $this->observe('ratio', -INF);
    }

    public function testUsesObserveOutsideOfAProperty(): void
    {
        $this->observe('size', 1);
    }
}
