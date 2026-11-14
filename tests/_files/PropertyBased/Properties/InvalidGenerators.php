<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Properties;

use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

abstract class InvalidGenerators
{
    /**
     * @return ValueGenerator<int>
     */
    public static function generatorWithRequiredParameter(int $maximum): ValueGenerator
    {
        return Generators::integers(0, $maximum);
    }

    /**
     * @return ValueGenerator<int>
     */
    abstract public static function abstractGenerator(): ValueGenerator;

    /**
     * @return ValueGenerator<int>
     */
    public function nonStaticGenerator(): ValueGenerator
    {
        return Generators::integers();
    }
}
