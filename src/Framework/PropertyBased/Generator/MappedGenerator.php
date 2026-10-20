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

/**
 * @template T
 * @template U
 *
 * @template-extends AbstractGenerator<U>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class MappedGenerator extends AbstractGenerator
{
    /**
     * @var AbstractGenerator<T>
     */
    private AbstractGenerator $generator;

    /**
     * @var Closure(T): U
     */
    private Closure $mapper;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param AbstractGenerator<T> $generator
     * @param Closure(T): U        $mapper
     */
    public function __construct(AbstractGenerator $generator, Closure $mapper)
    {
        $this->label = self::labelOf('map', $generator->label());

        $this->generator = $generator;
        $this->mapper    = $mapper;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): mixed
    {
        return ($this->mapper)($this->generator->generate($source));
    }
}
