<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Extensions_Story
 * @author     Mattis Stordalen Flister <mattis@xait.no>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.0
 */

/**
 * A story test case.
 *
 * @package    PHPUnit
 * @subpackage Extensions_Story
 * @author     Mattis Stordalen Flister <mattis@xait.no>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
abstract class PHPUnit_Extensions_Story_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var    PHPUnit_Extensions_Story_Scenario
     */
    protected $scenario;

    /**
     * @var    array
     */
    protected $world = array();

    /**
     * Constructs a test case with the given name.
     *
     * @param  string $name
     * @param  array  $data
     * @param  string $dataName
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->scenario = new PHPUnit_Extensions_Story_Scenario($this);
    }

    /**
     * @method PHPUnit_Extensions_Story_Step and($contextOrOutcome)
     */
    public function __call($command, $arguments)
    {
        switch ($command) {
            case 'and': {
                return $this->scenario->_and($arguments);
            }
            break;

            default: {
                throw new BadMethodCallException(
                  "Method $command not defined."
                );
            }
        }
    }

    /**
     * Returns this test's scenario.
     *
     * @return PHPUnit_Extensions_Story_Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     *
     *
     */
    protected function notImplemented($action)
    {
        if (strstr($action, ' ')) {
            $this->markTestIncomplete("step: $action not implemented.");
        }

        throw new BadMethodCallException("Method $action not defined.");
    }

    /**
     * Adds a "Given" step to the scenario.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    protected function given($context)
    {
        return $this->scenario->given(func_get_args());
    }

    /**
     * Adds a "When" step to the scenario.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    protected function when($event)
    {
        return $this->scenario->when(func_get_args());
    }

    /**
     * Adds a "Then" step to the scenario.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    protected function then($outcome)
    {
        return $this->scenario->then(func_get_args());
    }

    /**
     * Add another step of the same type as the step that was added before.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    protected function _and($contextOrOutcome)
    {
        return $this->scenario->_and(func_get_args());
    }

    /**
     * Run this test's scenario.
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected function runTest()
    {
        $testResult = parent::runTest();
        $this->scenario->run($this->world);
        return $testResult;
    }

    /**
     * Implementation for "Given" steps.
     *
     * @param  array  $world
     * @param  string $action
     * @param  array  $arguments
     */
    abstract protected function runGiven(&$world, $action, $arguments);

    /**
     * Implementation for "When" steps.
     *
     * @param  array  $world
     * @param  string $action
     * @param  array  $arguments
     */
    abstract protected function runWhen(&$world, $action, $arguments);

    /**
     * Implementation for "Then" steps.
     *
     * @param  array  $world
     * @param  string $action
     * @param  array  $arguments
     */
    abstract protected function runThen(&$world, $action, $arguments);
}
