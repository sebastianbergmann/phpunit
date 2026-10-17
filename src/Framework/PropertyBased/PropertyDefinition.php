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
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\TestCase;

/**
 * A property after its metadata has been validated: the generator for each
 * of its parameters that is derived from its type or where it comes from
 * otherwise, its explicit examples, the minimum percentages of its examples
 * that are required to be classified with labels, and the methods of its test
 * case class that are registered with #[GeneratorFor].
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
     * @var array<non-empty-string, AbstractGenerator<mixed>|Closure(): ValueGenerator<mixed>|GeneratorMethod>
     */
    private array $parameters;

    /**
     * @var list<ExplicitExample>
     */
    private array $explicitExamples;

    /**
     * @var list<array{label: non-empty-string, minimumPercentage: float}>
     */
    private array $requiredClassifications;

    /**
     * @var array<class-string, GeneratorMethod>
     */
    private array $registeredGenerators;

    /**
     * @param class-string<TestCase>                                                                             $className
     * @param non-empty-string                                                                                   $methodName
     * @param array<non-empty-string, AbstractGenerator<mixed>|Closure(): ValueGenerator<mixed>|GeneratorMethod> $parameters              by parameter name, in the order of the parameters, the generators that are derived from types and the closures and methods that are specified with #[ForAll]
     * @param list<ExplicitExample>                                                                              $explicitExamples        the examples that are specified with #[TestWith] and #[TestWithJson], in the order in which they are specified
     * @param list<array{label: non-empty-string, minimumPercentage: float}>                                     $requiredClassifications the minimum percentages of the examples that are required with #[RequireClassification] to be classified with labels
     * @param array<class-string, GeneratorMethod>                                                               $registeredGenerators    the methods that are registered with #[GeneratorFor], by the class they are registered for
     */
    public function __construct(string $className, string $methodName, array $parameters, array $explicitExamples, array $requiredClassifications, array $registeredGenerators)
    {
        $this->className               = $className;
        $this->methodName              = $methodName;
        $this->parameters              = $parameters;
        $this->explicitExamples        = $explicitExamples;
        $this->requiredClassifications = $requiredClassifications;
        $this->registeredGenerators    = $registeredGenerators;
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
     * @return array<non-empty-string, AbstractGenerator<mixed>|Closure(): ValueGenerator<mixed>|GeneratorMethod>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return list<ExplicitExample>
     */
    public function explicitExamples(): array
    {
        return $this->explicitExamples;
    }

    /**
     * @return list<array{label: non-empty-string, minimumPercentage: float}>
     */
    public function requiredClassifications(): array
    {
        return $this->requiredClassifications;
    }

    /**
     * @return array<class-string, GeneratorMethod>
     */
    public function registeredGenerators(): array
    {
        return $this->registeredGenerators;
    }
}
