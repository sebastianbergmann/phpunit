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
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DataSetFilter.php';
require_once 'PHPUnit/Extensions/Database/Constraint/DataSetIsEqual.php';

/**
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */
class Extensions_Database_DataSet_FilterTest extends PHPUnit_Framework_TestCase
{
    protected $expectedDataSet;

    public function setUp()
    {
        $this->expectedDataSet = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(
            dirname(__FILE__).'/../_files/XmlDataSets/FilteredTestFixture.xml'
        );
    }

    public function testDeprecatedFilteredDataSetConstructor()
    {
        $constraint = new PHPUnit_Extensions_Database_Constraint_DataSetIsEqual($this->expectedDataSet);
        $dataSet = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(
            dirname(__FILE__).'/../_files/XmlDataSets/FilteredTestComparison.xml'
        );

        $filteredDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet, array(
            'table1' => array('table1_id'),
            'table2' => '*',
            'table3' => 'table3_id'
        ));

        self::assertThat($filteredDataSet, $constraint);
    }

    public function testExcludeFilteredDataSet()
    {
        $constraint = new PHPUnit_Extensions_Database_Constraint_DataSetIsEqual($this->expectedDataSet);
        $dataSet = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(
            dirname(__FILE__).'/../_files/XmlDataSets/FilteredTestComparison.xml'
        );

        $filteredDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);

        $filteredDataSet->addExcludeTables(array('table2'));
        $filteredDataSet->setExcludeColumnsForTable('table1', array('table1_id'));
        $filteredDataSet->setExcludeColumnsForTable('table3', array('table3_id'));

        self::assertThat($filteredDataSet, $constraint);
    }

    public function testIncludeFilteredDataSet()
    {
        $constraint = new PHPUnit_Extensions_Database_Constraint_DataSetIsEqual($this->expectedDataSet);
        $dataSet = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(
            dirname(__FILE__).'/../_files/XmlDataSets/FilteredTestComparison.xml'
        );

        $filteredDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);

        $filteredDataSet->addIncludeTables(array('table1', 'table3'));
        $filteredDataSet->setIncludeColumnsForTable('table1', array('column1', 'column2', 'column3', 'column4'));
        $filteredDataSet->setIncludeColumnsForTable('table3', array('column9', 'column10', 'column11', 'column12'));

        self::assertThat($filteredDataSet, $constraint);
    }

    public function testIncludeExcludeMixedDataSet()
    {
        $constraint = new PHPUnit_Extensions_Database_Constraint_DataSetIsEqual($this->expectedDataSet);
        $dataSet = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(
            dirname(__FILE__).'/../_files/XmlDataSets/FilteredTestComparison.xml'
        );

        $filteredDataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($dataSet);

        $filteredDataSet->addIncludeTables(array('table1', 'table3'));
        $filteredDataSet->setExcludeColumnsForTable('table1', array('table1_id'));
        $filteredDataSet->setIncludeColumnsForTable('table3', array('column9', 'column10', 'column11', 'column12'));

        self::assertThat($filteredDataSet, $constraint);
    }
}
?>