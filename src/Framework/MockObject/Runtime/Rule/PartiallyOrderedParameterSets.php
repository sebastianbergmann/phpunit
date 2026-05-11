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

use function array_key_exists;
use function array_values;
use function count;
use function is_array;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\MockObject\NoMoreParameterSetsConfiguredException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PartiallyOrderedParameterSets implements ParametersRule
{
    /**
     * @var list<IndexedParameters>
     */
    private array $stack = [];

    /**
     * @var list<IndexedParameters>
     */
    private array $applied                     = [];
    private ?OrderedParameterSets $ordered     = null;
    private ?UnorderedParameterSets $unordered = null;
    private int $numberOfConfiguredParameterSets;
    private int $numberOfInvocations = 0;

    /**
     * @param list<mixed> $stack
     */
    public function __construct(array $stack)
    {
        $ordered   = [];
        $unordered = [];

        foreach ($stack as $index => $parameters) {
            if (is_array($parameters)) {
                $strict = false;

                if (count($parameters) === 1 && array_key_exists('pinned', $parameters)) {
                    $strict     = true;
                    $parameters = $parameters['pinned'];

                    if (is_array($parameters) === false) {
                        $parameters = [$parameters];
                    }
                }

                $parameters = new IndexedParameters(array_values($parameters), $index, $strict);
            } else {
                $parameters = new IndexedParameters([$parameters], $index, false);
            }

            if ($parameters->isStrict() === true) {
                $ordered[] = $parameters;

                continue;
            }

            $unordered[] = $parameters;
        }

        // when we only have ordered sets we use OrderedParameterSets
        if (count($ordered) > 0 && count($unordered) === 0) {
            $this->ordered = new OrderedParameterSets($ordered);
        } else {
            $this->stack = $ordered;
        }

        if (count($unordered) > 0) {
            $this->unordered = new UnorderedParameterSets($unordered);
        }

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

        $stack = $this->stack;

        foreach ($stack as $index => $parameters) {
            if ($parameters->at() === $this->numberOfInvocations - 1) {
                unset($stack[$index]);

                $this->stack     = array_values($stack);
                $this->applied[] = $parameters;

                $parameters->apply($invocation);

                return;
            }
        }

        if ($this->ordered !== null) {
            $this->ordered->apply($invocation);
        }

        if ($this->unordered !== null) {
            $this->unordered->apply($invocation);
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
        if ($this->unordered !== null) {
            $this->unordered->verify();
        }

        if ($this->ordered !== null) {
            $this->ordered->verify();
        }

        foreach ($this->applied as $parameters) {
            $parameters->verify();
        }
    }
}
