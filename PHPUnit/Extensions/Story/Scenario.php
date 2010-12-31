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
 * A scenario.
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
class PHPUnit_Extensions_Story_Scenario
{
    /**
     * @var    PHPUnit_Extensions_Story_TestCase
     */
    protected $test;

    /**
     * @var    array
     */
    protected $steps = array();

    /**
     * @var    string
     */
    protected $lastCalledMethod;

    /**
     * Constructor.
     *
     * @param  PHPUnit_Extensions_Story_TestCase $caller
     */
    public function __construct($test)
    {
        if ($test instanceof PHPUnit_Extensions_Story_TestCase ||
            $test instanceof PHPUnit_Extensions_Story_SeleniumTestCase) {
            $this->test = $test;
        } else {
            throw new Exception('$test must either be PHPUnit_Extensions_Story_TestCase or PHPUnit_Extensions_Story_SeleniumTestCase');
        }
    }

    /**
     * Adds a "Given" step to the scenario.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    public function given($arguments)
    {
        return $this->addStep(new PHPUnit_Extensions_Story_Given($arguments));
    }

    /**
     * Adds a "When" step to the scenario.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    public function when($arguments)
    {
        return $this->addStep(new PHPUnit_Extensions_Story_When($arguments));
    }

    /**
     * Adds a "Then" step to the scenario.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    public function then($arguments)
    {
        return $this->addStep(new PHPUnit_Extensions_Story_Then($arguments));
    }

    /**
     * Add another step of the same type as the step that was added before.
     *
     * @param  array $arguments
     * @return PHPUnit_Extensions_Story_TestCase
     */
    public function _and($arguments)
    {
        $lastCalledStepClass = get_class($this->steps[count($this->steps)-1]);

        return $this->addStep(new $lastCalledStepClass($arguments));
    }

    /**
     * Runs this scenario.
     *
     * @param  array $world
     */
    public function run(array &$world)
    {
        foreach ($this->steps as $step)
        {
            if ($step instanceof PHPUnit_Extensions_Story_Given) {
                $this->test->runGiven(
                  $world, $step->getAction(), $step->getArguments()
                );
            }

            else if ($step instanceof PHPUnit_Extensions_Story_When) {
                $this->test->runWhen(
                  $world, $step->getAction(), $step->getArguments()
                );
            }

            else {
                $this->test->runThen(
                  $world, $step->getAction(), $step->getArguments()
                );
            }
        }
    }

    /**
     * Adds a step to the scenario.
     *
     * @param  PHPUnit_Extensions_Story_Step $step
     * @return PHPUnit_Extensions_Story_TestCase
     */
    protected function addStep(PHPUnit_Extensions_Story_Step $step)
    {
        $this->steps[] = $step;

        return $this->test;
    }

    /**
     * Returns the steps of this scenario.
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }
}
