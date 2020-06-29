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
        $this->valueMap = \array_filter($valueMap, '\is_array');
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

        throw $this->getExpectationFailedException($invocation->getParameters());
    }

    public function toString(): string
    {
        return 'return value from a map';
    }

    private function getExpectationFailedException(
        array $actualArguments
    ): ExpectationFailedException {
        $exporter          = new Exporter();
        $expectedArguments = $this->getExpectedArguments();

        return new ExpectationFailedException(
            'method arguments did not match to any of mocked',
            new ComparisonFailure(
                $expectedArguments,
                $actualArguments,
                $exporter->shortenedExport($expectedArguments),
                $exporter->shortenedExport($actualArguments)
            )
        );
    }

    private function getExpectedArguments(): array
    {
        $expectedArguments = [];

        foreach ($this->valueMap as $map) {
            \array_pop($map);
            $expectedArguments[] = $map;
        }

        return $expectedArguments;
    }
}
