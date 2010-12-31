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
 * Prints stories in HTML format.
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
class PHPUnit_Extensions_Story_ResultPrinter_HTML extends PHPUnit_Extensions_Story_ResultPrinter
{
    /**
     * @var boolean
     */
    protected $printsHTML = TRUE;

    /**
     * @var integer
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $scenarios = '';

    /**
     * @var string
     */
    protected $templatePath;

    /**
     * Constructor.
     *
     * @param  mixed   $out
     * @throws InvalidArgumentException
     */
    public function __construct($out = NULL)
    {
        parent::__construct($out);

        $this->templatePath = sprintf(
          '%s%sTemplate%s',

          dirname(__FILE__),
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR
        );
    }

    /**
     * Handler for 'start class' event.
     *
     * @param  string $name
     */
    protected function startClass($name)
    {
        $scenarioHeaderTemplate = new Text_Template(
          $this->templatePath . 'scenario_header.html'
        );

        $scenarioHeaderTemplate->setVar(
          array(
            'name' => $this->currentTestClassPrettified
          )
        );

        $this->scenarios .= $scenarioHeaderTemplate->render();
    }

    /**
     * Handler for 'on test' event.
     *
     * @param  string  $name
     * @param  boolean $success
     * @param  array   $steps
     */
    protected function onTest($name, $success = TRUE, array $steps = array())
    {
        if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
            $scenarioStatus = 'scenarioFailed';
        }

        else if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED) {
            $scenarioStatus = 'scenarioSkipped';
        }

        else if ($this->testStatus == PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
            $scenarioStatus = 'scenarioIncomplete';
        }

        else {
            $scenarioStatus = 'scenarioSuccess';
        }

        $lastStepName = '';
        $stepsBuffer  = '';

        foreach ($steps as $step) {
            $currentStepName = $step->getName();

            if ($lastStepName == $currentStepName) {
                $stepText = 'and';
            } else {
                $stepText = $currentStepName;
            }

            $lastStepName = $currentStepName;

            $stepTemplate = new Text_Template(
              $this->templatePath . 'step.html'
            );

            $stepTemplate->setVar(
              array(
                'text'   => $stepText,
                'action' => $step->getAction() . ' ' . $step->getArguments(TRUE),
              )
            );

            $stepsBuffer .= $stepTemplate->render();
        }

        $scenarioTemplate = new Text_Template(
          $this->templatePath . 'scenario.html'
        );

        $scenarioTemplate->setVar(
          array(
            'id'             => ++$this->id,
            'name'           => $name,
            'scenarioStatus' => $scenarioStatus,
            'steps'          => $stepsBuffer,
          )
        );

        $this->scenarios .= $scenarioTemplate->render();
    }

    /**
     * Handler for 'end run' event.
     *
     */
    protected function endRun()
    {
        $scenariosTemplate = new Text_Template(
          $this->templatePath . 'scenarios.html'
        );

        $scenariosTemplate->setVar(
          array(
            'scenarios'           => $this->scenarios,
            'successfulScenarios' => $this->successful,
            'failedScenarios'     => $this->failed,
            'skippedScenarios'    => $this->skipped,
            'incompleteScenarios' => $this->incomplete
          )
        );

        $this->write($scenariosTemplate->render());
    }
}
