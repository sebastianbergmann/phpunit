<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

/**
 * An example that falsified a property: the choices from which its arguments
 * were generated, its arguments, the values that it drew, and the steps of its
 * state machine, exported as PHP code, or the explicit example that falsified
 * the property.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Counterexample
{
    /**
     * @var list<non-negative-int>
     */
    private array $choiceSequence;

    /**
     * @var array<non-empty-string, string>
     */
    private array $arguments;

    /**
     * @var null|int|non-empty-string
     */
    private null|int|string $explicitExample;

    /**
     * @var list<DrawnValue>
     */
    private array $drawnValues;

    /**
     * @var list<non-empty-string>
     */
    private array $steps;

    /**
     * @param list<non-negative-int>          $choiceSequence
     * @param array<non-empty-string, string> $arguments
     * @param null|int|non-empty-string       $explicitExample
     * @param list<DrawnValue>                $drawnValues
     * @param list<non-empty-string>          $steps
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(array $choiceSequence, array $arguments, null|int|string $explicitExample, array $drawnValues, array $steps)
    {
        $this->choiceSequence  = $choiceSequence;
        $this->arguments       = $arguments;
        $this->explicitExample = $explicitExample;
        $this->drawnValues     = $drawnValues;
        $this->steps           = $steps;
    }

    /**
     * The choices from which the arguments of the counterexample were
     * generated. It is empty for an explicit example, whose arguments are not
     * generated.
     *
     * @return list<non-negative-int>
     */
    public function choiceSequence(): array
    {
        return $this->choiceSequence;
    }

    /**
     * The arguments of the counterexample, exported as PHP code, by the name
     * of the parameter they were passed to.
     *
     * @return array<non-empty-string, string>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * The name of the explicit example, specified with #[TestWith] or
     * #[TestWithJson], that is the counterexample, or its number among the
     * explicit examples for which no name is specified. Null when the
     * counterexample was generated.
     *
     * @return null|int|non-empty-string
     */
    public function explicitExample(): null|int|string
    {
        return $this->explicitExample;
    }

    /**
     * The values that the counterexample drew with draw(), in the order in
     * which they were drawn. It is empty for an explicit example, for which
     * values cannot be drawn.
     *
     * @return list<DrawnValue>
     */
    public function drawnValues(): array
    {
        return $this->drawnValues;
    }

    /**
     * The steps of the state machine that the counterexample ran with
     * runStateMachine(), written as PHP code that calls the methods of the
     * state machine, which is referred to as $machine, in the order in which
     * they were taken, followed by the invariant that failed, if one did. It
     * is empty when the counterexample did not run a state machine.
     *
     * @return list<non-empty-string>
     */
    public function steps(): array
    {
        return $this->steps;
    }
}
