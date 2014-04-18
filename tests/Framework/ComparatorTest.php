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
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

class TestClass {}
class TestClassComparator extends PHPUnit_Framework_Comparator_Object {}

/**
 * Don't use other test methods than ->fail() here, because the testers tested
 * here are the foundation for the other test methods
 *
 * @package    PHPUnit
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
class Framework_ComparatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers       PHPUnit_Framework_ComparatorFactory
     * @dataProvider instanceProvider
     */
    public function testGetInstance($a, $b, $expected)
    {
        $factory = new PHPUnit_Framework_ComparatorFactory;

        if (get_class($factory->getComparatorFor($a, $b)) != $expected) {
            $this->fail();
        }
    }

    /**
     * @covers PHPUnit_Framework_ComparatorFactory
     */
    public function testRegister()
    {
        $comparator = new TestClassComparator;

        $factory = new PHPUnit_Framework_ComparatorFactory;
        $factory->register($comparator);

        $a = new TestClass;
        $b = new TestClass;
        $expected = 'TestClassComparator';

        if (get_class($factory->getComparatorFor($a, $b)) != $expected) {
            $factory->unregister($comparator);
            $this->fail();
        }

        $factory->unregister($comparator);
    }

    /**
     * @covers PHPUnit_Framework_ComparatorFactory
     */
    public function testUnregister()
    {
        $comparator = new TestClassComparator;

        $factory = new PHPUnit_Framework_ComparatorFactory;
        $factory->register($comparator);
        $factory->unregister($comparator);

        $a = new TestClass;
        $b = new TestClass;
        $expected = 'PHPUnit_Framework_Comparator_Object';

        if (get_class($factory->getComparatorFor($a, $b)) != $expected) {
            var_dump(get_class($factory->getComparatorFor($a, $b)));
            $this->fail();
        }
    }

    public function instanceProvider()
    {
        $tmpfile = tmpfile();

        return array(
            array(null, null, 'PHPUnit_Framework_Comparator_Scalar'),
            array(null, true, 'PHPUnit_Framework_Comparator_Scalar'),
            array(true, null, 'PHPUnit_Framework_Comparator_Scalar'),
            array(true, true, 'PHPUnit_Framework_Comparator_Scalar'),
            array(false, false, 'PHPUnit_Framework_Comparator_Scalar'),
            array(true, false, 'PHPUnit_Framework_Comparator_Scalar'),
            array(false, true, 'PHPUnit_Framework_Comparator_Scalar'),
            array('', '', 'PHPUnit_Framework_Comparator_Scalar'),
            array('0', '0', 'PHPUnit_Framework_Comparator_Scalar'),
            array('0', 0, 'PHPUnit_Framework_Comparator_Numeric'),
            array(0, '0', 'PHPUnit_Framework_Comparator_Numeric'),
            array(0, 0, 'PHPUnit_Framework_Comparator_Numeric'),
            array(1.0, 0, 'PHPUnit_Framework_Comparator_Double'),
            array(0, 1.0, 'PHPUnit_Framework_Comparator_Double'),
            array(1.0, 1.0, 'PHPUnit_Framework_Comparator_Double'),
            array(array(1), array(1), 'PHPUnit_Framework_Comparator_Array'),
            array($tmpfile, $tmpfile, 'PHPUnit_Framework_Comparator_Resource'),
            array(new stdClass, new stdClass, 'PHPUnit_Framework_Comparator_Object'),
            array(new DateTime, new DateTime, 'PHPUnit_Framework_Comparator_DateTime'),
            array(new SplObjectStorage, new SplObjectStorage, 'PHPUnit_Framework_Comparator_SplObjectStorage'),
            array(new Exception, new Exception, 'PHPUnit_Framework_Comparator_Exception'),
            array(new DOMDocument, new DOMDocument, 'PHPUnit_Framework_Comparator_DOMNode'),
            array(new DOMNode, new DOMNode, 'PHPUnit_Framework_Comparator_DOMNode'),
            // mixed types
            array($tmpfile, array(1), 'PHPUnit_Framework_Comparator_Type'),
            array(array(1), $tmpfile, 'PHPUnit_Framework_Comparator_Type'),
            array($tmpfile, '1', 'PHPUnit_Framework_Comparator_Type'),
            array('1', $tmpfile, 'PHPUnit_Framework_Comparator_Type'),
            array($tmpfile, new stdClass, 'PHPUnit_Framework_Comparator_Type'),
            array(new stdClass, $tmpfile, 'PHPUnit_Framework_Comparator_Type'),
            array(new stdClass, array(1), 'PHPUnit_Framework_Comparator_Type'),
            array(array(1), new stdClass, 'PHPUnit_Framework_Comparator_Type'),
            array(new stdClass, '1', 'PHPUnit_Framework_Comparator_Type'),
            array('1', new stdClass, 'PHPUnit_Framework_Comparator_Type'),
            array(new ClassWithToString, '1', 'PHPUnit_Framework_Comparator_Scalar'),
            array('1', new ClassWithToString, 'PHPUnit_Framework_Comparator_Scalar'),
            array(1.0, new stdClass, 'PHPUnit_Framework_Comparator_Type'),
            array(new stdClass, 1.0, 'PHPUnit_Framework_Comparator_Type'),
            array(1.0, array(1), 'PHPUnit_Framework_Comparator_Type'),
            array(array(1), 1.0, 'PHPUnit_Framework_Comparator_Type'),
        );
    }
}
