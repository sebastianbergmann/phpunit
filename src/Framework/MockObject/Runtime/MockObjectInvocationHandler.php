<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function array_unique;
use function array_values;
use function in_array;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\MockObject\Rule\MethodName;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class MockObjectInvocationHandler extends InvocationHandler
{
    /**
     * @throws TestDoubleSealedException
     */
    public function expects(InvocationOrder $rule): InvocationMocker
    {
        if ($this->isSealed()) {
            throw new TestDoubleSealedException;
        }

        $matcher = new Matcher($rule);
        $this->addMatcher($matcher);

        return new InvocationMockerImplementation(
            $this,
            $matcher,
            ...$this->configurableMethods,
        );
    }

    public function seal(): void
    {
        if ($this->isSealed()) {
            return;
        }

        $this->markSealed();

        $configuredMethods = $this->configuredMethodNames();

        foreach ($this->configurableMethods as $method) {
            if (!in_array($method->name(), $configuredMethods, true)) {
                $matcher = new Matcher(new InvokedCount(0));

                $matcher->setMethodNameRule(new MethodName($method->name()));

                $this->addMatcher($matcher);
            }
        }
    }

    /**
     * Returns the list of method names that have been configured with expectations.
     * Only considers exact string matches for method names.
     * Methods with any() expectation are considered configured.
     *
     * @return list<non-empty-string>
     */
    private function configuredMethodNames(): array
    {
        $names = [];

        foreach ($this->matchers() as $matcher) {
            if (!$matcher->hasMethodNameRule()) {
                continue;
            }

            foreach ($this->configurableMethods as $method) {
                if ($matcher->methodNameRule()->matchesName($method->name())) {
                    $names[] = $method->name();
                }
            }
        }

        return array_values(array_unique($names));
    }
}
