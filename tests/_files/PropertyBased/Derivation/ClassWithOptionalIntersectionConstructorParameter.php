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

use ArrayIterator;
use Countable;
use Iterator;

final readonly class ClassWithOptionalIntersectionConstructorParameter
{
    public function __construct(public int $value, public Countable&Iterator $values = new ArrayIterator)
    {
    }
}
