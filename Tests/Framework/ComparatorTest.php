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
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.6.0
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithToString.php';

class TestClass {}
class TestClassComparator extends PHPUnit_Framework_Comparator_Object {}

/**
 *
 *
 * @package    PHPUnit
 * @author     Bernhard Schussek <bschussek@2bepublished.at>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.6.0
 */
class Framework_ComparatorTest extends PHPUnit_Framework_TestCase
{
    // Don't use other test methods than ->fail() here, because the testers tested
    // here are the foundation for the other test methods

    public function instanceProvider()
    {
        $tmpfile = tmpfile();
        $type = new PHPUnit_Framework_Comparator_Type;
        $scalar = new PHPUnit_Framework_Comparator_Scalar;
        $double = new PHPUnit_Framework_Comparator_Double;
        $array = new PHPUnit_Framework_Comparator_Array;
        $object = new PHPUnit_Framework_Comparator_Object;
        $resource = new PHPUnit_Framework_Comparator_Resource;
        $dom = new PHPUnit_Framework_Comparator_DOMDocument;
        $exception = new PHPUnit_Framework_Comparator_Exception;
        $storage = new PHPUnit_Framework_Comparator_SplObjectStorage;

        return array(
            array(null, null, $scalar),
            array(null, true, $scalar),
            array(true, null, $scalar),
            array(true, true, $scalar),
            array(false, false, $scalar),
            array(true, false, $scalar),
            array(false, true, $scalar),
            array('', '', $scalar),
            array('0', '0', $scalar),
            array('0', 0, $scalar),
            array(0, '0', $scalar),
            array(0, 0, $scalar),
            array(1.0, 0, $double),
            array(0, 1.0, $double),
            array(1.0, 1.0, $double),
            array(array(1), array(1), $array),
            array($tmpfile, $tmpfile, $resource),
            array(new stdClass, new stdClass, $object),
            array(new SplObjectStorage, new SplObjectStorage, $storage),
            array(new Exception, new Exception, $exception),
            array(new DOMDocument, new DOMDocument, $dom),
            // mixed types
            array($tmpfile, array(1), $type),
            array(array(1), $tmpfile, $type),
            array($tmpfile, '1', $type),
            array('1', $tmpfile, $type),
            array($tmpfile, new stdClass, $type),
            array(new stdClass, $tmpfile, $type),
            array(new stdClass, array(1), $type),
            array(array(1), new stdClass, $type),
            array(new stdClass, '1', $type),
            array('1', new stdClass, $type),
            array(new ClassWithToString, '1', $scalar),
            array('1', new ClassWithToString, $scalar),
            array(1.0, new stdClass, $type),
            array(new stdClass, 1.0, $type),
            array(1.0, array(1), $type),
            array(array(1), 1.0, $type),
        );
    }

    /**
     * @dataProvider instanceProvider
     */
    public function testGetInstance($a, $b, $expected)
    {
        if (PHPUnit_Framework_Comparator::getInstance($a, $b) != $expected) {
            $this->fail();
        }
    }

    public function testRegister()
    {
        $comparator = new TestClassComparator;
        PHPUnit_Framework_Comparator::register($comparator);
        $a = new TestClass;
        $b = new TestClass;
        $expected = new TestClassComparator;

        if (PHPUnit_Framework_Comparator::getInstance($a, $b) != $expected) {
            PHPUnit_Framework_Comparator::unregister($comparator);
            $this->fail();
        }

        PHPUnit_Framework_Comparator::unregister($comparator);
    }

    public function testUnregister()
    {
        $comparator = new TestClassComparator;
        PHPUnit_Framework_Comparator::register($comparator);
        PHPUnit_Framework_Comparator::unregister($comparator);
        $a = new TestClass;
        $b = new TestClass;
        $expected = new PHPUnit_Framework_Comparator_Object;

        if (PHPUnit_Framework_Comparator::getInstance($a, $b) != $expected) {
            $this->fail();
        }
    }
}
