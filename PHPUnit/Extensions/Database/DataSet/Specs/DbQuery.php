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
 * @since      File available since Release 3.4.0
 */

require_once ('PHPUnit/Extensions/Database/DataSet/ISpec.php');
require_once ('PHPUnit/Extensions/Database/DB/DefaultDatabaseConnection.php');
require_once ('PHPUnit/Extensions/Database/IDatabaseListConsumer.php');

/**
 * Creates DefaultDataSets based off of a spec string.
 *
 * This spec class requires a list of databases to be set to the object before
 * it can return a list of databases.
 *
 * The format of the spec string is as follows:
 *
 * <db label>:<schema>:<table name>:<sql>
 *
 * The db label should be equal to one of the keys in the array of databases
 * passed to setDatabases().
 *
 * The schema should be the primary schema you will be running the sql query
 * against.
 *
 * The table name should be set to what you would like the table name in the
 * dataset to be.
 *
 * The sql is the query you want to use to generate the table columns and data.
 * The column names in the table will be identical to the column aliases in the
 * query.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2009 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Extensions_Database_DataSet_Specs_DbQuery implements PHPUnit_Extensions_Database_DataSet_ISpec, PHPUnit_Extensions_Database_IDatabaseListConsumer
{
    /**
     * @var array
     */
    protected $databases = array();

    /**
     * Sets the database for the spec
     *
     * @param array $databases
     */
    public function setDatabases(array $databases)
    {
        $this->databases = $databases;
    }

    /**
     * Creates a Default Data Set with a query table from a data set spec.
     *
     * @param string $dataSetSpec
     * @return PHPUnit_Extensions_Database_DataSet_DefaultDataSet
     */
    public function getDataSet($dataSetSpec)
    {
        $pdoRflc = new ReflectionClass('PDO');
        list($dbLabel, $schema, $table, $sql) = explode(':', $dataSetSpec, 4);

        $databaseInfo = $this->databases[$dbLabel];
        $pdo = $pdoRflc->newInstanceArgs(explode('|', $databaseInfo));

        $dbConnection = new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($pdo, $schema);
        $table = $dbConnection->createQueryTable($table, $sql);
        return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($table));
    }
}

?>