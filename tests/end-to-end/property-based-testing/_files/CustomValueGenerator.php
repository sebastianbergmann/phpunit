<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use Closure;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

/**
 * @implements ValueGenerator<int>
 */
final class CustomValueGenerator implements ValueGenerator
{
    public function map(Closure $mapper): ValueGenerator
    {
        return $this;
    }

    public function filter(Closure $predicate): ValueGenerator
    {
        return $this;
    }

    public function flatMap(Closure $mapper): ValueGenerator
    {
        return $this;
    }
}
