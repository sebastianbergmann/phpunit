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

use function implode;
use function sprintf;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use RuntimeException;
use Throwable;

/**
 * Reports that an object of a class, for which a generator was derived from
 * the types of the parameters of its constructor, cannot be created because
 * the constructor does not accept the arguments that were generated for it:
 * the constructor of a value object that validates its arguments, for
 * instance.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CannotCreateObjectException extends RuntimeException implements Exception
{
    /**
     * The arguments are exported like the arguments of a counterexample are,
     * with the names that classes have in the global namespace, as the
     * exception is not raised for a file that could import them.
     *
     * @param class-string                   $className
     * @param array<non-empty-string, mixed> $arguments     the generated arguments by the names of the constructor's parameters
     * @param Constructions                  $constructions the objects that generators created with new while the arguments were generated
     */
    public static function constructorRaisedException(string $className, array $arguments, Constructions $constructions, Throwable $throwable): self
    {
        $scope    = NameScope::forCallSite(false, false, null);
        $exporter = new CounterexampleExporter($scope, $constructions);
        $exported = [];

        foreach ($arguments as $name => $argument) {
            $exported[] = $name . ': ' . $exporter->export($argument);
        }

        return new self(
            sprintf(
                "The constructor of class %s raised %s for generated arguments: %s\n\n    new %s(%s)\n\nA generator that creates objects of this class with arguments that its constructor accepts can be registered with #[GeneratorFor]",
                $className,
                $throwable::class,
                $throwable->getMessage(),
                $scope->shortName($className),
                implode(', ', $exported),
            ),
            $throwable,
        );
    }

    private function __construct(string $message, Throwable $previous)
    {
        parent::__construct($message, 0, $previous);
    }
}
