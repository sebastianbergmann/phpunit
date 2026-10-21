<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function class_exists;
use function interface_exists;
use function method_exists;
use function sprintf;
use function trait_exists;
use Closure;
use PHPUnit\Metadata\ForAll;
use ReflectionMethod;
use Throwable;

/**
 * Validates the classes and the methods with which generators are created:
 * the generator that is specified with #[ForAll] or #[ForAllExternal] for a
 * parameter, the classes that are used with #[UseGenerators], and the methods
 * that are registered with #[GeneratorFor].
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class GeneratorMethodValidator
{
    /**
     * @throws InvalidGeneratorMethodException
     *
     * @return Closure(): ValueGenerator<mixed>|GeneratorMethod
     */
    public function specifiedWithForAll(ForAll $forAll): Closure|GeneratorMethod
    {
        $generator = $forAll->generator();

        if ($generator instanceof Closure) {
            return $generator;
        }

        $parameterName      = $forAll->parameterName();
        $generatorClassName = $forAll->className();

        $this->ensureIsClass(
            $generatorClassName,
            sprintf('the generator for parameter $%s is specified as a method of', $parameterName),
        );

        if ($generator === '') {
            throw InvalidGeneratorMethodException::because(
                sprintf('the generator for parameter $%s is specified as a method whose name is empty', $parameterName),
            );
        }

        if (!method_exists($generatorClassName, $generator)) {
            throw InvalidGeneratorMethodException::because(
                sprintf(
                    'the generator for parameter $%s is specified as %s::%s(), which does not exist',
                    $parameterName,
                    $generatorClassName,
                    $generator,
                ),
            );
        }

        $reason = $this->whyGeneratorCannotBeCreatedWith(new ReflectionMethod($generatorClassName, $generator));

        if ($reason !== null) {
            throw InvalidGeneratorMethodException::because(
                sprintf(
                    'the generator for parameter $%s is specified as %s::%s(), which %s',
                    $parameterName,
                    $generatorClassName,
                    $generator,
                    $reason,
                ),
            );
        }

        return new GeneratorMethod($generatorClassName, $generator);
    }

    /**
     * Reports a class on which methods that return generators are to be
     * called whose name is empty, that cannot be loaded, that does not exist,
     * or that is an interface or a trait, whose static methods cannot be
     * called.
     *
     * @param non-empty-string $usage the beginning of the message, which continues with the name of the class, for instance "#[UseGenerators] is used with"
     *
     * @throws InvalidGeneratorMethodException
     */
    public function ensureIsClass(string $generatorClassName, string $usage): void
    {
        if ($generatorClassName === '') {
            throw InvalidGeneratorMethodException::because(
                sprintf('%s a class whose name is empty', $usage),
            );
        }

        $reference = $usage . ' ' . $generatorClassName;

        try {
            $exists = class_exists($generatorClassName);
        } catch (Throwable $t) {
            // for instance, the class cannot be loaded because its parent
            // class does not exist
            throw InvalidGeneratorMethodException::because(
                sprintf('%s, which cannot be loaded, %s was raised: %s', $reference, $t::class, $t->getMessage()),
            );
        }

        if ($exists) {
            return;
        }

        // the autoloader has already been invoked by class_exists()
        if (interface_exists($generatorClassName, false)) {
            throw InvalidGeneratorMethodException::because(
                sprintf('%s, which is an interface, not a class', $reference),
            );
        }

        if (trait_exists($generatorClassName, false)) {
            throw InvalidGeneratorMethodException::because(
                sprintf('%s, which is a trait, not a class', $reference),
            );
        }

        throw InvalidGeneratorMethodException::because(
            sprintf('%s, which does not exist', $reference),
        );
    }

    /**
     * Returns why a generator cannot be created by calling a method on its
     * class without arguments, which is how the methods that are specified
     * with #[ForAll] or #[ForAllExternal] and the methods that are registered
     * with #[GeneratorFor] are called, or null if it can be.
     *
     * @return ?non-empty-string
     */
    public function whyGeneratorCannotBeCreatedWith(ReflectionMethod $method): ?string
    {
        if (!$method->isPublic() || !$method->isStatic()) {
            return 'is not public and static';
        }

        if ($method->isAbstract()) {
            return 'is abstract';
        }

        if ($method->getNumberOfRequiredParameters() > 0) {
            return 'has required parameters';
        }

        return null;
    }
}
