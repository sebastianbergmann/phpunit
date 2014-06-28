<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2010-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 */

/**
 * Invocation matcher which looks for sets of specific parameters in the invocations.
 *
 * Checks the parameters of the incoming invocations, the parameter list is
 * checked against the defined constraints in $parameters. If the constraint
 * is met it will return true in matches().
 *
 * It takes a list of match groups and and increases a call index after each invocation.
 * So the first invocation uses the first group of constraints, the second the next and so on.
 *
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2010-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 */
class PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters
  extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{

  /**
   * @var array
   */
  private $_parameterGroups = array();

  /**
   * @var array
   */
  private $_invocations = array();

  /**
   * @param array $parameterGroups
   */
  public function __construct(array $parameterGroups)
  {
      foreach ($parameterGroups as $index => $parameters) {
          foreach ($parameters as $parameter) {
              if (!($parameter instanceof \PHPUnit_Framework_Constraint))
              {
                  $parameter = new \PHPUnit_Framework_Constraint_IsEqual($parameter);
              }
              $this->_parameterGroups[$index][] = $parameter;
          }
      }
  }

    /**
     * @return string
     */
    public function toString()
    {
        $text = 'with consecutive parameters';

        return $text;
    }

  /**
   * @param PHPUnit_Framework_MockObject_Invocation $invocation
   * @return bool
   */
  public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
  {
      $this->_invocations[] = $invocation;
      $callIndex = count($this->_invocations) - 1;
      $this->verifyInvocation($invocation, $callIndex);
      return FALSE;
  }

  public function verify()
  {
      foreach ($this->_invocations as $callIndex => $invocation) {
        $this->verifyInvocation($invocation, $callIndex);
      }
  }

  /**
   * Verify a single invocation
   *
   * @param PHPUnit_Framework_MockObject_Invocation $invocation
   * @param int $callIndex
   * @throws PHPUnit_Framework_ExpectationFailedException
   */
  private function verifyInvocation(PHPUnit_Framework_MockObject_Invocation $invocation, $callIndex)
  {

      if (isset($this->_parameterGroups[$callIndex])) {
          $parameters = $this->_parameterGroups[$callIndex];
      } else {
        // no parameter assertion for this call index
        return;
      }

      if ($invocation === NULL) {
          throw new PHPUnit_Framework_ExpectationFailedException(
            'Mocked method does not exist.'
          );
      }

      if (count($invocation->parameters) < count($parameters)) {
          throw new PHPUnit_Framework_ExpectationFailedException(
              sprintf(
                'Parameter count for invocation %s is too low.',
                $invocation->toString()
              )
          );
      }

      foreach ($parameters as $i => $parameter) {
          $parameter->evaluate(
              $invocation->parameters[$i],
              sprintf(
                'Parameter %s for invocation #%d %s does not match expected ' .
                'value.',
                $i,
                $callIndex,
                $invocation->toString()
              )
          );
      }
  }
}
