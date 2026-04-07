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

use function array_pop;
use function count;
use function is_array;
use function sprintf;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Util\Exporter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ReturnValueMap implements Stub
{
    /**
     * @var array<mixed>
     */
    private array $valueMap;
    private bool $strict;

    /**
     * @param array<mixed> $valueMap
     */
    public function __construct(array $valueMap, bool $strict = false)
    {
        $this->valueMap = $valueMap;
        $this->strict   = $strict;
    }

    /**
     * @throws ExpectationFailedException
     */
    public function invoke(Invocation $invocation): mixed
    {
        $parameterCount = count($invocation->parameters());

        foreach ($this->valueMap as $map) {
            if (!is_array($map) || $parameterCount !== (count($map) - 1)) {
                continue;
            }

            $return = array_pop($map);

            if ($this->parametersMatch($map, $invocation->parameters())) {
                return $return;
            }
        }

        if ($this->strict) {
            throw new ExpectationFailedException(
                sprintf(
                    'No entry in the value map matched the invocation of %s::%s() with parameters (%s)',
                    $invocation->className(),
                    $invocation->methodName(),
                    Exporter::shortenedExport($invocation->parameters()),
                ),
            );
        }

        return null;
    }

    /**
     * @param array<int, mixed> $mapParameters
     * @param array<int, mixed> $invocationParameters
     */
    private function parametersMatch(array $mapParameters, array $invocationParameters): bool
    {
        foreach ($mapParameters as $i => $expected) {
            if ($expected instanceof Constraint) {
                if (!$expected->evaluate($invocationParameters[$i], '', true)) {
                    return false;
                }
            } elseif ($expected !== $invocationParameters[$i]) {
                return false;
            }
        }

        return true;
    }
}
