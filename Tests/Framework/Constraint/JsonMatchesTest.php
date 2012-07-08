<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  2002-2011 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.7.0
 */

/**
 * @package    PHPUnit
 * @author     Bastian Feder <php@bastian-feder.de>
 * @copyright  2011 Bastian Feder <php@bastian-feder.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.7.0
 */
class Framework_Constraint_JsonMatchesTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider evaluateDataprovider
     * @covers PHPUnit_Framework_Constraint_JsonMatches::evaluate
     * @covers PHPUnit_Framework_Constraint_JsonMatches::matches
     * @covers PHPUnit_Framework_Constraint_JsonMatches::__construct
     * @covers PHPUnit_Framework_Constraint_JsonMatches::getJsonError
     */
    public function testEvaluate($expected, $jsonOther, $jsonValue)
    {
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches($jsonValue);
        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', TRUE));
    }

    /**
     * @covers PHPUnit_Framework_Constraint_JsonMatches::toString
     */
    public function testToString()
    {
        $jsonValue = json_encode(array('Mascott' => 'Tux'));
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches($jsonValue);

        $this->assertEquals('matches JSON string "' . $jsonValue . '"', $constraint->toString());
    }


    public static function evaluateDataprovider()
    {
        return array(
            'valid JSON' => array(TRUE, json_encode(array('Mascott' => 'Tux')), json_encode(array('Mascott' => 'Tux'))),
            'error syntax' => array(FALSE, '{"Mascott"::}', json_encode(array('Mascott' => 'Tux'))),
            'error UTF-8' => array(FALSE, json_encode('\xB1\x31'), json_encode(array('Mascott' => 'Tux'))),
            'invalid JSON in class instantiation' => array(FALSE, json_encode(array('Mascott' => 'Tux')), '{"Mascott"::}'),
        );
    }
}
