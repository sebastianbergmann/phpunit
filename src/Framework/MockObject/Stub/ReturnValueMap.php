<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;

/**
 * Stubs a method by returning a value from a map.
 */
class ReturnValueMap implements Stub
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
        $parameterCount = \count($invocation->getParameters());

        foreach ($this->valueMap as $map) {
            if (!\is_array($map) || $parameterCount !== (\count($map) - 1)) {
                continue;
            }

            $return = \array_pop($map);

            if ($invocation->getParameters() === $map) {
                return $return;
            }
        }
    }

    public function toString(): string
    {
        return 'return value from a map';
    }
}
