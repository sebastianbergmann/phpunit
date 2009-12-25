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
require_once 'PHPUnit/Extensions/Database/DB/DefaultDatabaseConnection.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTable.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableMetaData.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/Operation/RowBased.php';

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'DatabaseTestUtility.php';


/**
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */
class Extensions_Database_Operation_RowBasedTest extends PHPUnit_Extensions_Database_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('PDO/SQLite is required to run this test.');
        }

        parent::setUp();
    }

    public function getConnection()
    {
        return new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection(DBUnitTestUtility::getSQLiteMemoryDB(), 'sqlite');
    }

    public function getDataSet()
    {
        $tables = array(
            new PHPUnit_Extensions_Database_DataSet_DefaultTable(
                new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table1',
                    array('table1_id', 'column1', 'column2', 'column3', 'column4'))
            ),
            new PHPUnit_Extensions_Database_DataSet_DefaultTable(
                new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table2',
                    array('table2_id', 'column5', 'column6', 'column7', 'column8'))
            ),
            new PHPUnit_Extensions_Database_DataSet_DefaultTable(
                new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table3',
                    array('table3_id', 'column9', 'column10', 'column11', 'column12'))
            ),
        );

        return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet($tables);
    }

    public function testExcecute()
    {
        $connection = $this->getConnection();
        /* @var $connection PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection */
        $table1 = new PHPUnit_Extensions_Database_DataSet_DefaultTable(
            new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table1', array('table1_id', 'column1', 'column2', 'column3', 'column4'))
        );

        $table1->addRow(array(
            'table1_id' => 1,
            'column1' => 'foo',
            'column2' => 42,
            'column3' => 4.2,
            'column4' => 'bar'
        ));

        $table1->addRow(array(
            'table1_id' => 2,
            'column1' => 'qwerty',
            'column2' => 23,
            'column3' => 2.3,
            'column4' => 'dvorak'
        ));

        $table2 = new PHPUnit_Extensions_Database_DataSet_DefaultTable(
            new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table2', array('table2_id', 'column5', 'column6', 'column7', 'column8'))
        );

        $table2->addRow(array(
            'table2_id' => 1,
            'column5' => 'fdyhkn',
            'column6' => 64,
            'column7' => 4568.64,
            'column8' => 'hkladfg'
        ));

        $dataSet = new PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($table1, $table2));

        $mockOperation = $this->getMock('PHPUnit_Extensions_Database_Operation_RowBased',
                array('buildOperationQuery', 'buildOperationArguments'));

        /* @var $mockOperation PHPUnit_Framework_MockObject_MockObject */
        $mockOperation->expects($this->at(0))
                ->method('buildOperationQuery')
                ->with($connection->createDataSet()->getTableMetaData('table1'), $table1)
                ->will(
                    $this->returnValue('INSERT INTO table1 (table1_id, column1, column2, column3, column4) VALUES (?, ?, ?, ?, ?)')
                );

        $mockOperation->expects($this->at(1))
                ->method('buildOperationArguments')
                ->with($connection->createDataSet()->getTableMetaData('table1'), $table1, 0)
                ->will(
                    $this->returnValue(array(1, 'foo', 42, 4.2, 'bar'))
                );

        $mockOperation->expects($this->at(2))
                ->method('buildOperationArguments')
                ->with($connection->createDataSet()->getTableMetaData('table1'), $table1, 1)
                ->will(
                    $this->returnValue(array(2, 'qwerty', 23, 2.3, 'dvorak'))
                );

        $mockOperation->expects($this->at(3))
                ->method('buildOperationQuery')
                ->with($connection->createDataSet()->getTableMetaData('table2'), $table2)
                ->will(
                    $this->returnValue('INSERT INTO table2 (table2_id, column5, column6, column7, column8) VALUES (?, ?, ?, ?, ?)')
                );

        $mockOperation->expects($this->at(4))
                ->method('buildOperationArguments')
                ->with($connection->createDataSet()->getTableMetaData('table2'), $table2, 0)
                ->will(
                    $this->returnValue(array(1, 'fdyhkn', 64, 4568.64, 'hkladfg'))
                );

        /* @var $mockOperation PHPUnit_Extensions_Database_Operation_RowBased */
        $mockOperation->execute($connection, $dataSet);

        $this->assertDataSetsEqual(new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet(dirname(__FILE__).'/../_files/XmlDataSets/RowBasedExecute.xml'), $connection->createDataSet(array('table1', 'table2')));
    }
}
?>
