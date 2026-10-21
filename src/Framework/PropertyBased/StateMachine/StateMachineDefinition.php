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
 * A state machine after its metadata has been validated: the methods that are
 * called once before its steps are taken (#[Initialize]), its rules
 * (#[Rule]), and its invariants (#[Invariant]), each in the order in which it
 * is declared.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class StateMachineDefinition
{
    /**
     * @var class-string
     */
    private string $className;

    /**
     * @var list<StepDefinition>
     */
    private array $initializations;

    /**
     * @var non-empty-list<StepDefinition>
     */
    private array $rules;

    /**
     * @var list<non-empty-string>
     */
    private array $invariants;

    /**
     * @var list<non-empty-string>
     */
    private array $invariantsAtEnd;

    /**
     * @param class-string                   $className
     * @param list<StepDefinition>           $initializations
     * @param non-empty-list<StepDefinition> $rules
     * @param list<non-empty-string>         $invariants      the methods of the invariants that are checked after every step
     * @param list<non-empty-string>         $invariantsAtEnd the methods of the invariants that are only checked after the initialization and after the last step
     */
    public function __construct(string $className, array $initializations, array $rules, array $invariants, array $invariantsAtEnd)
    {
        $this->className       = $className;
        $this->initializations = $initializations;
        $this->rules           = $rules;
        $this->invariants      = $invariants;
        $this->invariantsAtEnd = $invariantsAtEnd;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * @return list<StepDefinition>
     */
    public function initializations(): array
    {
        return $this->initializations;
    }

    /**
     * @return non-empty-list<StepDefinition>
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @return list<non-empty-string>
     */
    public function invariants(): array
    {
        return $this->invariants;
    }

    /**
     * @return list<non-empty-string>
     */
    public function invariantsAtEnd(): array
    {
        return $this->invariantsAtEnd;
    }
}
