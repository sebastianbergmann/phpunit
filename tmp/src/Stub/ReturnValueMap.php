<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stubs a method by returning a value from a map.
 */
class PHPUnit_Framework_MockObject_Stub_ReturnValueMap implements PHPUnit_Framework_MockObject_Stub
{
    protected $valueMap;

    public function __construct(array $valueMap)
    {
        $this->valueMap = $valueMap;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
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
