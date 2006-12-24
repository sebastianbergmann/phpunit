<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 *             Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

if (!interface_exists('PHPUnit_Framework_Constraint')) {

/**
 * Abstract base class for constraints. which are placed upon any value.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 *             Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Interface available since Release 3.0.0
 */
abstract class PHPUnit_Framework_Constraint implements PHPUnit_Framework_SelfDescribing
{
    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     * @abstract
     */
    abstract public function evaluate($other);

    /**
     * Creates the appropriate exception for the constraint which can be caught
     * by the unit test system. This can be called if a call to evaluate() fails.
     *
     * @param   mixed   $other The value passed to evaluate() which failed the
     *                         constraint check.
     * @param   string  $description A string with extra description of what was
     *                               going on while the evaluation failed.
     * @param   boolean $not Flag to indicate negation.
     * @throws  PHPUnit_Framework_ExpectationFailedException
     */
    public function fail($other, $description, $not = FALSE)
    {
        throw new PHPUnit_Framework_ExpectationFailedException(
          $this->failureDescription($other, $description, $not)
        );
    }

    protected function failureDescription($other, $description, $not)
    {
        $failureDescription = sprintf(
          'Failed asserting that %s %s.',

           PHPUnit_Util_Type::toString($other),
           $this->toString()
        );

        if ($not) {
            $failureDescription = self::negate($failureDescription);
        }

        if (!empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        return $failureDescription;
    }

    public static function negate($string)
    {
        return str_replace(
          array(
            'contains ',
            'exists',
            'has ',
            'is ',
            'matches '
          ),
          array(
            'does not contain ',
            'does not exist',
            'does not have ',
            'is not ',
            'does not match '
          ),
          $string
        );
    }
}

}

require_once 'PHPUnit/Framework/Constraint/And.php';
require_once 'PHPUnit/Framework/Constraint/ArrayHasKey.php';
require_once 'PHPUnit/Framework/Constraint/FileExists.php';
require_once 'PHPUnit/Framework/Constraint/GreaterThan.php';
require_once 'PHPUnit/Framework/Constraint/IsAnything.php';
require_once 'PHPUnit/Framework/Constraint/IsEqual.php';
require_once 'PHPUnit/Framework/Constraint/IsIdentical.php';
require_once 'PHPUnit/Framework/Constraint/IsInstanceOf.php';
require_once 'PHPUnit/Framework/Constraint/IsType.php';
require_once 'PHPUnit/Framework/Constraint/LessThan.php';
require_once 'PHPUnit/Framework/Constraint/Not.php';
require_once 'PHPUnit/Framework/Constraint/ObjectHasAttribute.php';
require_once 'PHPUnit/Framework/Constraint/Or.php';
require_once 'PHPUnit/Framework/Constraint/PCREMatch.php';
require_once 'PHPUnit/Framework/Constraint/StringContains.php';
require_once 'PHPUnit/Framework/Constraint/TraversableContains.php';
require_once 'PHPUnit/Framework/Constraint/Xor.php';
?>
