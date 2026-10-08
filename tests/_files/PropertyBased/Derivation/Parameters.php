<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Derivation;

use Countable;
use Iterator;
use PHPUnit\TestFixture\PropertyBased\Currency;

final class Parameters
{
    /**
     * @param non-empty-string $documented
     * @param list<Currency>   $currencies
     */
    public function method(string $documented, int $native, ?int $nullable, int|string $union, array $currencies, $untyped, Countable&Iterator $intersection, ClassWithUnsupportedConstructorParameter $nested): void
    {
    }

    public function methodWithVariadicParameter(int ...$values): void
    {
    }

    /**
     * @param list<int $values
     */
    public function methodWithUnparsableParameterType(array $values): void
    {
    }
}
