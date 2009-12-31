<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.3.0
 */

require_once 'PHPUnit/Extensions/Story/TestCase.php';
require_once 'BowlingGame.php';

class BowlingGameSpec extends PHPUnit_Extensions_Story_TestCase
{
    /**
     * @scenario
     */
    public function scoreForGutterGameIs0()
    {
        $this->given('New game')
             ->then('Score should be', 0);
    }

    /**
     * @scenario
     */
    public function scoreForAllOnesIs20()
    {
        $this->given('New game')
             ->when('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->and('Player rolls', 1)
             ->then('Score should be', 20);
    }

    /**
     * @scenario
     */
    public function scoreForOneSpareAnd3Is16()
    {
        $this->given('New game')
             ->when('Player rolls', 5)
             ->and('Player rolls', 5)
             ->and('Player rolls', 3)
             ->then('Score should be', 16);
    }

    /**
     * @scenario
     */
    public function scoreForOneStrikeAnd3And4Is24()
    {
        $this->given('New game')
             ->when('Player rolls', 10)
             ->and('Player rolls', 3)
             ->and('Player rolls', 4)
             ->then('Score should be', 24);
    }

    /**
     * @scenario
     */
    public function scoreForPerfectGameIs300()
    {
        $this->given('New game')
             ->when('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->and('Player rolls', 10)
             ->then('Score should be', 300);
    }

    public function runGiven(&$world, $action, $arguments)
    {
        switch($action) {
            case 'New game': {
                $world['game']  = new BowlingGame;
                $world['rolls'] = 0;
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }

    public function runWhen(&$world, $action, $arguments)
    {
        switch($action) {
            case 'Player rolls': {
                $world['game']->roll($arguments[0]);
                $world['rolls']++;
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }

    public function runThen(&$world, $action, $arguments)
    {
        switch($action) {
            case 'Score should be': {
                for ($i = $world['rolls']; $i < 20; $i++) {
                    $world['game']->roll(0);
                }

                $this->assertEquals($arguments[0], $world['game']->score());
            }
            break;

            default: {
                return $this->notImplemented($action);
            }
        }
    }
}

