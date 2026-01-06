<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Rule;

use function array_search;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\MockObject\NoMoreParameterSetsConfiguredException;

final class UnorderedParameterSets implements ParametersRule
{
    /**
     * @var list<Parameters>
     */
    private array $stack = [];

    /**
     * @var list<Parameters>
     */
    private array $unapplied = [];

    /**
     * @var list<Parameters>
     */
    private array $applied = [];
    private int $numberOfConfiguredParameterSets;

    /**
     * @param list<Parameters> $stack
     */
    public function __construct(array $stack)
    {
        foreach ($stack as $parameters) {
            $this->stack[] = new Parameters(is_array($parameters) ? $parameters : [$parameters]);
        }

        $this->unapplied                       = $this->stack;
        $this->numberOfConfiguredParameterSets = count($stack);
    }

    public function apply(BaseInvocation $invocation): void
    {
        if ($this->unapplied === []) {
            throw new NoMoreParameterSetsConfiguredException(
                $invocation,
                $this->numberOfConfiguredParameterSets,
            );
        }

        $checkedParameters   = 0;
        $unappliedParameters = count($this->unapplied);

        while ($checkedParameters < $unappliedParameters) {
            $checkedParameters++;
            $parameters = array_shift($this->unapplied);

            try {
                $parameters->useAssertionCount(false);
                $parameters->apply($invocation);

                $this->applied[] = $parameters;

                $parameters->useAssertionCount(true);
                $parameters->apply($invocation);

                break;
            } catch (ExpectationFailedException $e) {
                $this->unapplied[] = $parameters;
            }
        }
    }

    /**
     * Checks if the invocation $invocation matches the current rules. If it
     * does the rule will get the invoked() method called which should check
     * if an expectation is met.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        if (count($this->applied) !== $this->numberOfConfiguredParameterSets &&
            count($this->unapplied) > 0) {
            $unappliedIndexes = [];

            foreach ($this->unapplied as $parameters) {
                $unappliedIndexes[] = array_search($parameters, $this->stack, true);
            }

            throw new ExpectationFailedException(
                sprintf(
                    '%d out of %d expected parameter set%s %s called, index%s [' . implode(', ', $unappliedIndexes) . '] %s not called.',
                    count($this->applied),
                    $this->numberOfConfiguredParameterSets,
                    $this->numberOfConfiguredParameterSets !== 1 ? 's' : '',
                    count($this->applied) !== 1 ? 'were' : 'was',
                    count($unappliedIndexes) !== 1 ? 'es' : '',
                    count($unappliedIndexes) !== 1 ? 'were' : 'was',
                ),
            );
        }
    }
}
