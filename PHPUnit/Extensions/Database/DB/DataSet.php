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
 * @version    SVN: $Id:DataSet.php 1254 2009-09-02 04:36:15Z mlively $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

require_once 'PHPUnit/Extensions/Database/DataSet/AbstractDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableMetaData.php';
require_once 'PHPUnit/Extensions/Database/DB/TableIterator.php';
require_once 'PHPUnit/Extensions/Database/DB/Table.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Provides access to a database instance as a data set.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2009 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Extensions_Database_DB_DataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{

    /**
     * An array of ITable objects.
     *
     * @var array
     */
    protected $tables = array();

    /**
     * The database connection this dataset is using.
     *
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected $databaseConnection;

    /**
     * Creates a new dataset using the given database connection.
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection
     */
    public function __construct(PHPUnit_Extensions_Database_DB_IDatabaseConnection $databaseConnection)
    {
        $this->databaseConnection = $databaseConnection;
    }

    /**
     * Creates the query necessary to pull all of the data from a table.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData
     * @return unknown
     */
    public static function buildTableSelect(PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData)
    {
        if ($tableMetaData->getTableName() == '') {
            $e = new Exception("Empty Table Name");
            echo $e->getTraceAsString();
            throw $e;
        }

        $columnList = implode(', ', $tableMetaData->getColumns());

        $primaryKeys = $tableMetaData->getPrimaryKeys();
        if (count($primaryKeys)) {
            $orderBy = 'ORDER BY ' . implode(' ASC, ', $primaryKeys) . ' ASC';
        } else {
            $orderBy = '';
        }

        return "SELECT {$columnList} FROM {$tableMetaData->getTableName()} {$orderBy}";
    }

    /**
     * Creates an iterator over the tables in the data set. If $reverse is
     * true a reverse iterator will be returned.
     *
     * @param bool $reverse
     * @return PHPUnit_Extensions_Database_DB_TableIterator
     */
    protected function createIterator($reverse = FALSE)
    {
        return new PHPUnit_Extensions_Database_DB_TableIterator($this->getTableNames(), $this, $reverse);
    }

    /**
     * Returns a table object for the given table.
     *
     * @param string $tableName
     * @return PHPUnit_Extensions_Database_DB_Table
     */
    public function getTable($tableName)
    {
        if (!in_array($tableName, $this->getTableNames())) {
            throw new InvalidArgumentException("$tableName is not a table in the current database.");
        }

        if (empty($this->tables[$tableName])) {
            $this->tables[$tableName] = new PHPUnit_Extensions_Database_DB_Table($this->getTableMetaData($tableName), $this->databaseConnection);
        }

        return $this->tables[$tableName];
    }

    /**
     * Returns a table meta data object for the given table.
     *
     * @param string $tableName
     * @return PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData
     */
    public function getTableMetaData($tableName)
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $this->databaseConnection->getMetaData()->getTableColumns($tableName), $this->databaseConnection->getMetaData()->getTablePrimaryKeys($tableName));
    }

    /**
     * Returns a list of table names for the database
     *
     * @return Array
     */
    public function getTableNames()
    {
        return $this->databaseConnection->getMetaData()->getTableNames();
    }
}
?>
