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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Exporter\Exporter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ReturnValueMap implements Stub
{
    /**
     * @var array[]
     */
    private $valueMap;

    public function __construct(array $valueMap)
    {
        $this->valueMap = \array_values(\array_filter($valueMap, '\is_array'));
    }

    public function invoke(Invocation $invocation)
    {
        $parameterCount = \count($invocation->getParameters());

        foreach ($this->valueMap as $map) {
            if ($parameterCount !== (\count($map) - 1)) {
                continue;
            }

            $return = \array_pop($map);

            if ($invocation->getParameters() === $map) {
                return $return;
            }
        }

        throw $this->getExpectationFailedException($invocation);
    }

    public function toString(): string
    {
        return 'return value from a map';
    }

    private function getExpectationFailedException(
        Invocation $invocation
    ): ExpectationFailedException {
        $exporter          = new Exporter();
        $expectedArguments = $this->getExpectedArguments();
        $actualArguments   = $invocation->getParameters();

        return new ExpectationFailedException(
            \sprintf(
                'Arguments passed to %s::%s were not expected by ReturnValueMap',
                $invocation->getClassName(),
                $invocation->getMethodName()
            ),
            new ComparisonFailure(
                $expectedArguments,
                $actualArguments,
                $exporter->export($expectedArguments),
                $exporter->export($actualArguments)
            )
        );
    }

    private function getExpectedArguments(): array
    {
        // just first map, if exists
        if ($map = $this->valueMap[0] ?? null) {
            \array_pop($map);

            return $map;
        }

        return [];
    }
}
