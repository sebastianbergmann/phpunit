<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\Invocation;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ReturnValueMap implements Stub
{
    /**
     * @var array
     */
    private $valueMap;

    public function __construct(array $valueMap)
    {
        $this->valueMap = $valueMap;
    }

    public function invoke(Invocation $invocation)
    {
        $parameters = $invocation->getParameters();

        $parameterCount = \count($parameters);

        foreach ($this->valueMap as $map) {
            if (!\is_array($map) || $parameterCount !== (\count($map) - 1)) {
                continue;
            }

            $returnValue = $this->getReturnValue(\array_pop($map));

            if ($this->compare($parameters, $map)) {
                return $returnValue->invoke($invocation);
            }
        }
    }

    public function toString(): string
    {
        return 'return value from a map';
    }

    private function compare(array $actual, array $expected): bool
    {
        foreach ($expected as $index => $value) {
            if ($value instanceof Constraint) {
                if ($value->evaluate($actual[$index], '', true) === false) {
                    return false;
                }
            } else {
                if ($value !== $actual[$index]) {
                    return false;
                }
            }
        }

        return true;
    }

    private function getReturnValue($value): Stub
    {
        if (!$value instanceof Stub) {
            return new ReturnStub($value);
        }

        return $value;
    }
}
