<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/QueryDataSet.php';
require_once dirname(__FILE__). '/../_files/DatabaseTestUtility.php';
//require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTable.php';
//require_once 'PHPUnit/Extensions/Database/DB/DefaultDatabaseConnection.php';

/**
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */
class Extensions_Database_DataSet_QueryDataSetTest extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var PHPUnit_Extensions_Database_DataSet_QueryDataSet
     */
    protected $dataSet;

    protected $pdo;

    /**
     * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->pdo, 'test');
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/../_files/XmlDataSets/QueryDataSetTest.xml');
    }

    public function setUp()
    {
        $this->pdo = DBUnitTestUtility::getSQLiteMemoryDB();
        parent::setUp();
        $this->dataSet = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $this->dataSet->addTable('table1');
        $this->dataSet->addTable('query1', '
            SELECT
                t1.column1 tc1, t2.column5 tc2
            FROM
                table1 t1
                JOIN table2 t2 ON t1.table1_id = t2.table2_id
        ');
    }

    public function testGetTable()
    {
        $expectedTable1 = $this->getConnection()->createDataSet(array('table1'))->getTable('table1');

        $expectedTable2 = new PHPUnit_Extensions_Database_DataSet_DefaultTable(
            new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('query1', array('tc1', 'tc2'))
        );

        $expectedTable2->addRow(array('tc1' => 'bar', 'tc2' => 'blah'));

        $this->assertTablesEqual($expectedTable1, $this->dataSet->getTable('table1'));
        $this->assertTablesEqual($expectedTable2, $this->dataSet->getTable('query1'));
    }

    public function testGetTableNames()
    {
        $this->assertEquals(array('table1', 'query1'), $this->dataSet->getTableNames());
    }

    public function testCreateIterator()
    {
        $expectedTable1 = $this->getConnection()->createDataSet(array('table1'))->getTable('table1');

        $expectedTable2 = new PHPUnit_Extensions_Database_DataSet_DefaultTable(
            new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('query1', array('tc1', 'tc2'))
        );

        $expectedTable2->addRow(array('tc1' => 'bar', 'tc2' => 'blah'));

        foreach ($this->dataSet as $i => $table) {
            /* @var $table PHPUnit_Extensions_Database_DataSet_ITable */
            switch ($table->getTableMetaData()->getTableName()) {
                case 'table1':
                    $this->assertTablesEqual($expectedTable1, $table);
                    break;
                case 'query1':
                    $this->assertTablesEqual($expectedTable2, $table);
                    break;
                default:
                    $this->fail('Proper keys not present from the iterator');
            }
        }
    }
}
?>
