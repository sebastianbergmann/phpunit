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
require_once 'PHPUnit/Extensions/Database/DataSet/QueryTable.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTable.php';
require_once 'PHPUnit/Extensions/Database/DB/DefaultDatabaseConnection.php';

/**
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */
class Extensions_Database_DataSet_QueryTableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Extensions_Database_DataSet_QueryTable
     */
    protected $table;

    public function setUp()
    {
        $query = "
            SELECT
                'value1' as col1,
                'value2' as col2,
                'value3' as col3
            UNION SELECT
                'value4' as col1,
                'value5' as col2,
                'value6' as col3
        ";
        $this->table = new PHPUnit_Extensions_Database_DataSet_QueryTable(
            'table1',
            $query,
            new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection(new PDO('sqlite::memory:'), 'test')
        );
    }

    public static function providerTestGetValue()
    {
        return array(
            array(0, 'col1', 'value1'),
            array(0, 'col2', 'value2'),
            array(0, 'col3', 'value3'),
            array(1, 'col1', 'value4'),
            array(1, 'col2', 'value5'),
            array(1, 'col3', 'value6'),
        );
    }

    public function testGetTableMetaData()
    {
        $metaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table1', array('col1', 'col2', 'col3'));

        $this->assertEquals($metaData, $this->table->getTableMetaData());
    }

    public function testGetRowCount()
    {
        $this->assertEquals(2, $this->table->getRowCount());
    }

    /**
     * @dataProvider providerTestGetValue
     */
    public function testGetValue($row, $column, $value)
    {
        $this->assertEquals($value, $this->table->getValue($row, $column));
    }

    public function testGetRow()
    {
        $this->assertEquals(array('col1' => 'value1', 'col2' => 'value2', 'col3' => 'value3'), $this->table->getRow(0));
    }

    public function testAssertEquals()
    {
        $expected_table = new PHPUnit_Extensions_Database_DataSet_DefaultTable(new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table1', array('col1', 'col2', 'col3')));
        $expected_table->addRow(array('col1' => 'value1', 'col2' => 'value2', 'col3' => 'value3'));
        $expected_table->addRow(array('col1' => 'value4', 'col2' => 'value5', 'col3' => 'value6'));
        $this->assertTrue($this->table->assertEquals($expected_table));
    }

    public function testAssertEqualsFails()
    {
        $this->setExpectedException('Exception', 'Expected row count of 2, has a row count of 3');

        $expected_table = new PHPUnit_Extensions_Database_DataSet_DefaultTable(new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table1', array('col1', 'col2', 'col3')));
        $expected_table->addRow(array('col1' => 'value1', 'col2' => 'value2', 'col3' => 'value3'));
        $expected_table->addRow(array('col1' => 'value4', 'col2' => 'value5', 'col3' => 'value6'));
        $expected_table->addRow(array('col1' => 'value7', 'col2' => 'value8', 'col3' => 'value9'));
        $this->table->assertEquals($expected_table);
    }
}
?>