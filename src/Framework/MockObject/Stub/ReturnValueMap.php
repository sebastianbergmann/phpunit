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
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\InvocationResolver;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ReturnValueMap implements Stub
{
    /**
     * @var array
     */
    private $valueMap;

    /**
     * @var InvocationResolver
     */
    private $invocationResolver;

    public function __construct(array $valueMap, InvocationResolver $invocationResolver)
    {
        $this->valueMap           = $valueMap;
        $this->invocationResolver = $invocationResolver;
    }

    public function invoke(Invocation $invocation)
    {
        $parameterCount = count($invocation->getParameters());

        foreach ($this->valueMap as $map) {
            if (!is_array($map) || $parameterCount !== (count($map) - 1)) {
                continue;
            }

            $return = array_pop($map);

            if ($invocation->getParameters() === $map) {
                return $return;
            }
        }

        return $this->invocationResolver->defaultResult($invocation);
    }

    public function toString(): string
    {
        return 'return value from a map';
    }
}
