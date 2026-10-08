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

use Closure;
use PHPUnit\Framework\TestCase;

/**
 * A property after its metadata has been validated: the number of examples to
 * check, where the generator for each of its parameters comes from, and the
 * methods of its test case class that are registered with #[GeneratorFor].
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyDefinition
{
    /**
     * @var class-string<TestCase>
     */
    private string $className;

    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @var ?positive-int
     */
    private ?int $examples;

    /**
     * @var array<non-empty-string, null|Closure(): ValueGenerator<mixed>|non-empty-string>
     */
    private array $parameters;

    /**
     * @var array<class-string, non-empty-string>
     */
    private array $registeredGenerators;

    /**
     * @param class-string<TestCase>                                                          $className
     * @param non-empty-string                                                                $methodName
     * @param ?positive-int                                                                   $examples
     * @param array<non-empty-string, null|Closure(): ValueGenerator<mixed>|non-empty-string> $parameters           the generators that are specified with #[ForAll] by parameter name, in the order of the parameters, null for the parameters whose generator is derived from their type
     * @param array<class-string, non-empty-string>                                           $registeredGenerators the names of the methods that are registered with #[GeneratorFor] by class name
     */
    public function __construct(string $className, string $methodName, ?int $examples, array $parameters, array $registeredGenerators)
    {
        $this->className            = $className;
        $this->methodName           = $methodName;
        $this->examples             = $examples;
        $this->parameters           = $parameters;
        $this->registeredGenerators = $registeredGenerators;
    }

    /**
     * @return class-string<TestCase>
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * The number of examples to check, or null for the configured default.
     *
     * @return ?positive-int
     */
    public function examples(): ?int
    {
        return $this->examples;
    }

    /**
     * @return array<non-empty-string, null|Closure(): ValueGenerator<mixed>|non-empty-string>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<class-string, non-empty-string>
     */
    public function registeredGenerators(): array
    {
        return $this->registeredGenerators;
    }
}
