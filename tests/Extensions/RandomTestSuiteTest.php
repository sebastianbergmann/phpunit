<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Framework_Constraint
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 4.5.0
 */

/**
 * @package    PHPUnit
 * @subpackage Extensions
 * @author     Jose Armesto <jose@armesto.net>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 4.5.0
 * @covers     PHPUnit_Extensions_RandomTestSuite
 */
class Extensions_RandomTestSuiteTest extends PHPUnit_Framework_TestCase
{
    const CONSTANT_SEED = 0;

    public function testItRandomizesOrderOfTestSuitesInsideMainTestSuite()
    {
        $suite      = $this->givenTestSuiteWithFourTests();
        $randomizer = new PHPUnit_Extensions_RandomTestSuite;
        $randomizer->randomizeTestSuite($suite, self::CONSTANT_SEED);

        $this->assertOrderOfTestsIsNotTheSameAsBefore($suite->tests());
    }

    public function testItRandomizesOrderOfTestCasesInsideTestSuite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTest(new Success('test1'));
        $suite->addTest(new Success('test2'));
        $suite->addTest(new Success('test3'));
        $suite->addTest(new Success('test4'));
        $randomizer = new PHPUnit_Extensions_RandomTestSuite;

        $randomizer->randomizeTestSuite($suite, self::CONSTANT_SEED);

        $this->assertOrderOfTestsIsNotTheSameAsBefore($suite->tests());
    }

    private function givenTestSuiteWithFourTests()
    {
        $suite1 = new PHPUnit_Framework_TestSuite('test1');
        $suite1->addTest(new Success);
        $suite2 = new PHPUnit_Framework_TestSuite('test2');
        $suite2->addTest(new Success);
        $suite3 = new PHPUnit_Framework_TestSuite('test3');
        $suite3->addTest(new Success);
        $suite4 = new PHPUnit_Framework_TestSuite('test4');
        $suite4->addTest(new Success);
        $main_suite = new PHPUnit_Framework_TestSuite();
        $main_suite->addTest($suite1);
        $main_suite->addTest($suite2);
        $main_suite->addTest($suite3);
        $main_suite->addTest($suite4);

        return $main_suite;
    }

    private function assertOrderOfTestsIsNotTheSameAsBefore(array $tests)
    {
        $tests_names    = array($tests[0]->getName(), $tests[1]->getName(), $tests[2]->getName(), $tests[3]->getName());
        $default_order  = array('test1', 'test2', 'test3', 'test4');
        $this->assertNotEquals($default_order, $tests_names, 'The order must be different');
    }
}
