<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\StateMachine;

/**
 * A step of a state machine: a method with #[Rule], which can be taken any
 * number of times while its precondition holds, or a method with
 * #[Initialize], which is called once before the rules are taken.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class StepDefinition
{
    /**
     * @var non-empty-string
     */
    private string $methodName;

    /**
     * @var ?non-empty-string
     */
    private ?string $precondition;

    /**
     * @var ?non-empty-string
     */
    private ?string $produces;

    /**
     * @var list<ParameterDefinition>
     */
    private array $parameters;

    /**
     * @param non-empty-string          $methodName
     * @param ?non-empty-string         $precondition the method that tells whether the step can be taken, null when it can always be taken
     * @param ?non-empty-string         $produces     the pool to which the value that the step returns is added, null when it is not added to a pool
     * @param list<ParameterDefinition> $parameters
     */
    public function __construct(string $methodName, ?string $precondition, ?string $produces, array $parameters)
    {
        $this->methodName   = $methodName;
        $this->precondition = $precondition;
        $this->produces     = $produces;
        $this->parameters   = $parameters;
    }

    /**
     * @return non-empty-string
     */
    public function methodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return ?non-empty-string
     */
    public function precondition(): ?string
    {
        return $this->precondition;
    }

    /**
     * @return ?non-empty-string
     */
    public function produces(): ?string
    {
        return $this->produces;
    }

    /**
     * @return list<ParameterDefinition>
     */
    public function parameters(): array
    {
        return $this->parameters;
    }
}
