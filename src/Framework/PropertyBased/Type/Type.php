<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

/**
 * A type as it is written in PHPDoc, after it has been parsed. It is not
 * interpreted: class names are not resolved, and whether a generator can be
 * derived for the type is not known.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract readonly class Type
{
    /**
     * @return non-empty-string
     */
    abstract public function asString(): string;
}
