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

use function array_values;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;

/**
 * Generates objects of a class with new, using an argument of the respective
 * generator for each parameter of the class's constructor.
 *
 * When more objects of the class are nested in each other than allowed, for
 * instance for a node of a tree that takes child nodes, the example is
 * rejected.
 *
 * @template T of object
 *
 * @template-extends AbstractGenerator<T>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ObjectGenerator extends AbstractGenerator
{
    public const int MAXIMUM_NESTING = 5;

    /**
     * @var class-string<T>
     */
    private string $className;

    /**
     * @var array<non-empty-string, AbstractGenerator<mixed>>
     */
    private array $arguments;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @param class-string<T>                                   $className
     * @param array<non-empty-string, AbstractGenerator<mixed>> $arguments the generators for the arguments of the constructor by the names of its parameters, in the order of the parameters
     */
    public function __construct(string $className, array $arguments)
    {
        $this->label = self::labelOf('objects', $className);

        $this->className = $className;
        $this->arguments = $arguments;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): object
    {
        try {
            if ($source->startObject($this->className) > self::MAXIMUM_NESTING) {
                throw ExampleRejectedException::objectsNestedTooDeeply($this->className, self::MAXIMUM_NESTING);
            }

            $arguments = [];

            foreach ($this->arguments as $name => $generator) {
                $arguments[$name] = $generator->generate($source);
            }
        } finally {
            $source->endObject($this->className);
        }

        $object = new $this->className(...array_values($arguments));

        $source->constructions()->record($object, $this->className, $arguments);

        return $object;
    }
}
