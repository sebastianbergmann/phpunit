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

use PHPUnit\Framework\Exception;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationMockerImplementation extends AbstractInvocationImplementation implements InvocationMocker
{
    /**
     * @throws Exception
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     *
     * @return $this
     */
    public function with(mixed ...$arguments): InvocationMocker
    {
        $this->ensureParametersCanBeConfigured();

        $this->matcher->setParametersRule(new Rule\Parameters($arguments));

        return $this;
    }

    public function withParameterSetsInOrder(mixed ...$arguments): InvocationMocker
    {
        $this->ensureParametersCanBeConfigured();

        $this->matcher->setParametersRule(new Rule\OrderedParameterSets($arguments));

        return $this;
    }

    public function withParameterSetsInAnyOrder(mixed ...$arguments): InvocationMocker
    {
        $this->ensureParametersCanBeConfigured();

        $this->matcher->setParametersRule(new Rule\UnorderedParameterSets($arguments));

        return $this;
    }

    /**
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     *
     * @return $this
     */
    public function withAnyParameters(): InvocationMocker
    {
        $this->ensureParametersCanBeConfigured();

        $this->matcher->setParametersRule(new Rule\AnyParameters);

        return $this;
    }

    /**
     * @param non-empty-string $id
     *
     * @throws MatcherAlreadyRegisteredException
     *
     * @return $this
     */
    public function id(string $id): InvocationMocker
    {
        $this->invocationHandler->registerMatcher($id, $this->matcher);

        return $this;
    }

    /**
     * @param non-empty-string $id
     *
     * @return $this
     */
    public function after(string $id): InvocationMocker
    {
        $this->matcher->setAfterMatchBuilderId($id);

        return $this;
    }
}
