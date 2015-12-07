<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A set of expectation methods
 */
abstract class PHPUnit_Framework_Expect extends PHPUnit_Framework_Assert
{
    private static $failedExpecatations = [];

    public function __call($name, $args)
    {
        if (substr($name, 0, 6) === 'expect') {
            $method = 'assert' . substr($name, 6);
            if (is_callable([$this, $method])) {
                try {
                    call_user_func_array([$this, $method], $args);
                }  catch (PHPUnit_Framework_AssertionFailedError $e) {
                    self::$failedExpecatations[] = $e;
                }
                return;
            }
        }
        throw new \BadMethodCallException("Unknown static method $name called");
    }

    protected static function getFailedExpectations()
    {
        return self::$failedExpecatations;
    }

    protected static function resetFailedExpectations()
    {
        self::$failedExpecatations = [];
    }

}
