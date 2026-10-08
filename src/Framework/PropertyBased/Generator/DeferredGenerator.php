<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use Closure;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;

/**
 * A generator that is derived from a type only when it generates its first
 * value, by the deriver of the choice source. This way, the generators that
 * the test case class of the property registers for types are used, even for
 * the generators that are created while an example is generated.
 *
 * It also refers to the generator of a class from within the generators for
 * the arguments of the class's constructor, so that the construction of the
 * class can be recursive.
 *
 * @template-covariant T
 *
 * @template-extends AbstractGenerator<T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DeferredGenerator extends AbstractGenerator
{
    /**
     * @var Closure(GeneratorDeriver): AbstractGenerator<T>
     */
    private Closure $derive;
    private bool $recursive;

    /**
     * @param Closure(GeneratorDeriver): AbstractGenerator<T> $derive
     * @param bool                                            $recursive whether it refers to the generator of a class from within the generators for the arguments of the class's constructor
     */
    public function __construct(Closure $derive, bool $recursive)
    {
        $this->derive    = $derive;
        $this->recursive = $recursive;
    }

    /**
     * @return AbstractGenerator<T>
     */
    public function derive(GeneratorDeriver $deriver): AbstractGenerator
    {
        return ($this->derive)($deriver);
    }

    public function isRecursive(): bool
    {
        return $this->recursive;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        return $source->deriver()->deferred($this)->generate($source);
    }
}
