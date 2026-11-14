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

use PHPUnit\Framework\PropertyBased\CannotCreateObjectException;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use SebastianBergmann\Invoker\TimeoutException;
use Throwable;

/**
 * Generates objects of a class with new, using an argument of the respective
 * generator for each parameter of the class's constructor for which there is
 * a generator. The arguments are passed by the names of the parameters, so
 * that the constructor uses the default value of a parameter for which there
 * is no generator, also when there is one for a parameter after it.
 *
 * When more objects of the class are nested in each other than allowed, for
 * instance for a node of a tree that takes child nodes, the example is
 * rejected.
 *
 * When the constructor raises an exception for the generated arguments, as
 * the constructor of a value object that validates its arguments does for
 * most of them, the example is not rejected: the exception is reported with
 * the arguments, so that a generator for valid objects can be registered.
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
     * @param array<non-empty-string, AbstractGenerator<mixed>> $arguments the generators for the arguments of the constructor by the names of its parameters, in the order of the parameters, without the parameters whose default value is used
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

    /**
     * @throws CannotCreateObjectException
     */
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

        // a parameter that is passed by reference refers to the argument in
        // the array that is unpacked when the arguments are passed by name,
        // which must not change the arguments that are reported
        $namedArguments = $arguments;

        try {
            $object = new $this->className(...$namedArguments);
        } catch (ExampleRejectedException|TimeoutException $e) {
            // the example is rejected or exceeded its time limit while the
            // constructor ran, which is not an error of the constructor
            throw $e;
        } catch (Throwable $t) {
            throw CannotCreateObjectException::constructorRaisedException($this->className, $arguments, $source->constructions(), $t);
        }

        $source->constructions()->record($object, $this->className, $arguments);

        return $object;
    }
}
