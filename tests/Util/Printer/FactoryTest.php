<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @covers     PHPUnit_Util_Printer_Factory
 */
class PHPUnit_Util_Printer_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Util_Printer_Factory
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = new PHPUnit_Util_Printer_Factory();
    }

    public function testShouldReturnTheSamePrinterKeyWhenItIsAValidInstance()
    {
        $printerMock = $this
            ->getMockBuilder('PHPUnit_Util_Printer')
            ->disableOriginalConstructor()
            ->getMock();

        $options = array(
            'printer' => $printerMock,
        );

        $actualPrinter = $this->factory->getPrinter($options);
        $expectedPrinter = $options['printer'];

        $this->assertSame($expectedPrinter, $actualPrinter);
    }

    public function testShouldCreateAnInstanceOfDefaultObjectWhenOptionsAreEmpty()
    {
        $options = array();

        $actualPrinter = $this->factory->getPrinter($options);
        $expectedPrinterType = PHPUnit_Util_Printer_Factory::RESULT_PRINTER;

        $this->assertInstanceOf($expectedPrinterType, $actualPrinter);
    }

    public function testShouldWriteInStandardOutputByDefault()
    {
        $options = array();

        $printer = $this->factory->getPrinter($options);
        $expectedStream = 'php://stdout';

        $this->assertAttributeEquals($expectedStream, 'outTarget', $printer);
    }

    public function testShouldWriteInStandardErrorWhenDefined()
    {
        $options = array(
            'stderr' => true,
        );

        $printer = $this->factory->getPrinter($options);
        $expectedStream = 'php://stderr';

        $this->assertAttributeEquals($expectedStream, 'outTarget', $printer);
    }

    public function testShouldCreatePrinterInstanceBasedOnClassNameGivenByPrinterKey()
    {
        $printerMock = $this
            ->getMockBuilder('PHPUnit_Util_Printer')
            ->disableOriginalConstructor()
            ->getMock();

        $options = array(
            'printer' => get_class($printerMock),
        );

        $actualPrinter = $this->factory->getPrinter($options);
        $expectedPrinterType = $options['printer'];

        $this->assertInstanceOf($expectedPrinterType, $actualPrinter);
    }

    public function testShouldFactoryDefaultPrinterUsingTheGivenVerboseValue()
    {
        $options = array(
            'verbose' => true,
        );

        $printer = $this->factory->getPrinter($options);

        $this->assertAttributeEquals($options['verbose'], 'verbose', $printer);
    }

    public function testShouldFactoryDefaultPrinterUsingTheGivenColorsValue()
    {
        $options = array(
            'verbose'   => true,
            'colors'    => PHPUnit_TextUI_ResultPrinter::COLOR_ALWAYS,
        );

        $printer = $this->factory->getPrinter($options);
        $expectedColorFlag = true;

        $this->assertAttributeEquals($expectedColorFlag, 'colors', $printer);
    }

    public function testShouldFactoryDefaultPrinterUsingTheGivenDebugValue()
    {
        $options = array(
            'verbose'   => true,
            'colors'    => PHPUnit_TextUI_ResultPrinter::COLOR_ALWAYS,
            'debug'     => true,
        );

        $printer = $this->factory->getPrinter($options);

        $this->assertAttributeEquals($options['debug'], 'debug', $printer);
    }

    public function testShouldFactoryDefaultPrinterUsingTheGivenNumberOfColumns()
    {
        $options = array(
            'verbose'   => true,
            'colors'    => PHPUnit_TextUI_ResultPrinter::COLOR_ALWAYS,
            'debug'     => true,
            'columns'   => 10,
        );

        $printer = $this->factory->getPrinter($options);

        $this->assertAttributeEquals($options['columns'], 'numberOfColumns', $printer);
    }

    /**
     * @expectedException PHPUnit_Util_Exception
     * @expectedExceptionMessage "Invalid_Printer" was not found
     */
    public function testShouldThrowsAnExceptionWhenGivenPrinterCanNotBeFound()
    {
        $options = array(
            'printer' => 'Invalid_Printer',
        );

        $this->factory->getPrinter($options);
    }

    /**
     * @expectedException PHPUnit_Util_Exception
     * @expectedExceptionMessage "stdClass" is not a valid printer
     */
    public function testShouldThrowsAnExceptionWhenGivenPrinterIsNotAValidType()
    {
        $options = array(
            'printer' => 'stdClass',
        );

        $this->factory->getPrinter($options);
    }

    public function testShouldChoosePrinterOverPrinterClassKey()
    {
        $printerMock = $this
            ->getMockBuilder('PHPUnit_Util_Printer')
            ->disableOriginalConstructor()
            ->getMock();

        $options = array(
            'printer' => get_class($printerMock),
            'printerClass' => 'stdClass',
        );

        $actualPrinter = $this->factory->getPrinter($options);
        $expectedPrinterType = $options['printer'];

        $this->assertInstanceOf($expectedPrinterType, $actualPrinter);
    }

    public function testShouldUsePrinterClassKeyWhenPrinterKeyIsNotDefined()
    {
        $printerMock = $this
            ->getMockBuilder('PHPUnit_Util_Printer')
            ->disableOriginalConstructor()
            ->getMock();

        $options = array(
            'printerClass' => get_class($printerMock),
        );

        $actualPrinter = $this->factory->getPrinter($options);
        $expectedPrinterType = $options['printerClass'];

        $this->assertInstanceOf($expectedPrinterType, $actualPrinter);
    }

    /**
     * @expectedException PHPUnit_Util_Exception
     * @expectedExceptionMessage Could not load "IHopeThisIsNotAValidFile.php"
     */
    public function testShouldThrowsAnExceptionWhenPrinterFileCanNotBeLoaded()
    {
        $options = array(
            'printerFile' => 'IHopeThisIsNotAValidFile.php',
            'printerClass' => 'IHopeThisIsNotAValidPrinter',
        );

        $this->factory->getPrinter($options);
    }

    /**
     * @expectedException PHPUnit_Util_Exception
     * @expectedExceptionMessage Could not load "IHopeThisIsNotAValidPrinter.php"
     */
    public function testShouldLoadByFilenameWhenPrinterFileIsEmpty()
    {
        $options = array(
            'printerFile' => '',
            'printerClass' => 'IHopeThisIsNotAValidPrinter',
        );

        $this->factory->getPrinter($options);
    }
}
