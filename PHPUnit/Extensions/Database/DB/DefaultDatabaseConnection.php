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
 * @version    SVN: $Id:DefaultDatabaseConnection.php 1254 2009-09-02 04:36:15Z mlively $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

require_once 'PHPUnit/Extensions/Database/DB/IDatabaseConnection.php';
require_once 'PHPUnit/Extensions/Database/DB/MetaData.php';
require_once 'PHPUnit/Extensions/Database/DB/ResultSetTable.php';
require_once 'PHPUnit/Extensions/Database/DB/DataSet.php';
require_once 'PHPUnit/Extensions/Database/DB/FilteredDataSet.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Provides a basic interface for communicating with a database.
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
class PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection implements PHPUnit_Extensions_Database_DB_IDatabaseConnection
{
    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var string
     */
    protected $schema;

    /**
     * The metadata object used to retrieve table meta data from the database.
     *
     * @var PHPUnit_Extensions_Database_DB_IMetaData
     */
    protected $metaData;

    /**
     * Creates a new database connection
     *
     * @param PDO $connection
     * @param string $schema - The name of the database schema you will be testing against.
     */
    public function __construct(PDO $connection, $schema)
    {
        $this->connection = $connection;
        $this->metaData = PHPUnit_Extensions_Database_DB_MetaData::createMetaData($connection, $schema);
        $this->schema = $schema;

        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Close this connection.
     */
    public function close()
    {
        unset($this->connection);
    }

    /**
     * Returns a database metadata object that can be used to retrieve table
     * meta data from the database.
     *
     * @return PHPUnit_Extensions_Database_DB_IMetaData
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Returns the schema for the connection.
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Creates a dataset containing the specified table names. If no table
     * names are specified then it will created a dataset over the entire
     * database.
     *
     * @param array $tableNames
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     * @todo Implement the filtered data set.
     */
    public function createDataSet(Array $tableNames = NULL)
    {
        if (empty($tableNames)) {
            return new PHPUnit_Extensions_Database_DB_DataSet($this);
        } else {
            return new PHPUnit_Extensions_Database_DB_FilteredDataSet($this, $tableNames);
        }
    }

    /**
     * Creates a table with the result of the specified SQL statement.
     *
     * @param string $resultName
     * @param string $sql
     * @return PHPUnit_Extensions_Database_DB_Table
     */
    public function createQueryTable($resultName, $sql)
    {
        return new PHPUnit_Extensions_Database_DataSet_QueryTable($resultName, $sql, $this);
    }

    /**
     * Returns this connection database configuration
     *
     * @return PHPUnit_Extensions_Database_Database_DatabaseConfig
     */
    public function getConfig()
    {

    }

    /**
     * Returns a PDO Connection
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns the number of rows in the given table. You can specify an
     * optional where clause to return a subset of the table.
     *
     * @param string $tableName
     * @param string $whereClause
     * @param int
     */
    public function getRowCount($tableName, $whereClause = NULL)
    {
        $query = "SELECT COUNT(*) FROM {$tableName}";

        if (isset($whereClause)) {
            $query .= " WHERE {$whereClause}";
        }
    }

    /**
     * Returns a quoted schema object. (table name, column name, etc)
     *
     * @param string $object
     * @return string
     */
    public function quoteSchemaObject($object)
    {
        return $this->getMetaData()->quoteSchemaObject($object);
    }

    /**
     * Returns the command used to truncate a table.
     *
     * @return string
     */
    public function getTruncateCommand()
    {
        return $this->getMetaData()->getTruncateCommand();
    }

    /**
     * Returns true if the connection allows cascading
     *
     * @return bool
     */
    public function allowsCascading()
    {
        return $this->getMetaData()->allowsCascading();
    }
}
?>
