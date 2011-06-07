<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'ClassWithToString.php';

class TestClass {}
class TestClassComparator extends PHPUnit_Framework_Comparator_Object {}

// Don't use other test methods than ->fail() here, because the testers tested
// here are the foundation for the other test methods
class Framework_ComparatorTest extends PHPUnit_Framework_TestCase
{
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
