<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;

/**
 * Stubs a method by returning a value from a map.
 */
class PHPUnit_Framework_MockObject_Stub_ReturnValueMap implements Stub
{
    protected $valueMap;

    public function __construct(array $valueMap)
    {
        $this->valueMap = $valueMap;
    }

    public function invoke(Invocation $invocation)
    {
        $parameterCount = count($invocation->parameters);

        foreach ($this->valueMap as $map) {
            if (!is_array($map) || $parameterCount != count($map) - 1) {
                continue;
            }

            $return = array_pop($map);
            if ($invocation->parameters === $map) {
                return $return;
            }
        }

        return;
    }

    public function toString()
    {
        return 'return value from a map';
    }
}
