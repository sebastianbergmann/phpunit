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

use function count;
use function is_array;
use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\MockObject\NoMoreParameterSetsConfiguredException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PartialOrderedParameterSets implements ParametersRule
{
    /**
     * @var list<IndexedParameters>
     */
    private array $ordered = [];

    /**
     * @var list<IndexedParameters>
     */
    private array $applied = [];
    private UnorderedParameterSets $unordered;
    private int $numberOfConfiguredParameterSets;
    private int $numberOfInvocations = 0;

    /**
     * @param list<mixed> $stack
     */
    public function __construct(array $stack)
    {
        $unordered = [];

        foreach ($stack as $index => $parameters) {
            $parameters = ($parameters instanceof IndexedParameters)
                ? $parameters
                : new IndexedParameters(is_array($parameters) ? $parameters : [$parameters], false);
            $parameters->index($index);

            if ($parameters->isStrict() === true) {
                $this->ordered[] = $parameters;

                continue;
            }

            $unordered[] = $parameters;
        }

        $this->unordered                       = new UnorderedParameterSets($unordered);
        $this->numberOfConfiguredParameterSets = count($stack);
    }

    public function apply(BaseInvocation $invocation): void
    {
        $this->numberOfInvocations++;

        if ($this->numberOfInvocations > $this->numberOfConfiguredParameterSets) {
            throw new NoMoreParameterSetsConfiguredException(
                $invocation,
                $this->numberOfConfiguredParameterSets,
            );
        }

        foreach ($this->ordered as $key => $parameters) {
            if ($parameters->at() === $this->numberOfInvocations - 1) {
                unset($this->ordered[$key]);
                $this->applied[] = $parameters;
                $parameters->apply($invocation);

                return;
            }
        }

        $this->unordered->apply($invocation);
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
        $this->unordered->verify();

        if ($this->numberOfInvocations !== $this->numberOfConfiguredParameterSets &&
            count($this->ordered) + count($this->applied) === $this->numberOfConfiguredParameterSets &&
            $this->numberOfInvocations > 0) {
            throw new ExpectationFailedException(
                sprintf(
                    'Too many parameter sets given, %d out of %d expected parameter set%s %s been called.',
                    $this->numberOfInvocations,
                    $this->numberOfConfiguredParameterSets,
                    $this->numberOfConfiguredParameterSets !== 1 ? 's' : '',
                    $this->numberOfInvocations !== 1 ? 'have' : 'has',
                ),
            );
        }

        foreach ($this->applied as $parameters) {
            $parameters->verify();
        }
    }
}
