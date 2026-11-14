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

use Closure;

final readonly class ClassWithOptionalConstructorParameterBetweenOtherParameters
{
    public function __construct(public int $first, public ?Closure $callback = null, public int $last = 7)
    {
    }
}
