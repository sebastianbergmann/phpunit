<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;
use Closure;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

/**
 * Specifies the generator for a parameter of a property: either the name of a
 * public static method of the test case class that returns the generator, or
 * a static closure that returns it. A public static method of another class
 * is specified with #[ForAllExternal].
 *
 * @immutable
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class ForAll
{
    /**
     * @var Closure(): ValueGenerator<mixed>|non-empty-string
     */
    private Closure|string $generator;

    /**
     * @param Closure(): ValueGenerator<mixed>|non-empty-string $generator
     */
    public function __construct(Closure|string $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return Closure(): ValueGenerator<mixed>|non-empty-string
     */
    public function generator(): Closure|string
    {
        return $this->generator;
    }
}
