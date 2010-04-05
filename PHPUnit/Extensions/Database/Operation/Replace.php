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
 * @package    PHPUnit
 * @subpackage Extensions_Database_Operation
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

/**
 * Updates the rows in a given dataset using primary key columns.
 *
 * @package    PHPUnit
 * @subpackage Extensions_Database_Operation
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Extensions_Database_Operation_Replace extends PHPUnit_Extensions_Database_Operation_RowBased
{

    protected $operationName = 'REPLACE';

    protected function buildOperationQuery(PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData, PHPUnit_Extensions_Database_DataSet_ITable $table, PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection)
    {
        $keys = $databaseTableMetaData->getPrimaryKeys();

        $whereStatement = 'WHERE ' . implode(' AND ', $this->buildPreparedColumnArray($keys, $connection));

        $query = "
        	SELECT COUNT(*)
        	FROM {$connection->quoteSchemaObject($table->getTableMetaData()->getTableName())}
        	{$whereStatement}
        ";

        return $query;
    }

    protected function buildOperationArguments(PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData, PHPUnit_Extensions_Database_DataSet_ITable $table, $row)
    {
        $args = array();

        foreach ($databaseTableMetaData->getPrimaryKeys() as $columnName) {
            $args[] = $table->getValue($row, $columnName);
        }

        return $args;
    }

    /**
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     */
    public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        $insertOperation = new PHPUnit_Extensions_Database_Operation_Insert();
        $updateOperation = new PHPUnit_Extensions_Database_Operation_Update();

        $databaseDataSet = $connection->createDataSet();

        foreach ($dataSet as $table) {
            /* @var $table PHPUnit_Extensions_Database_DataSet_ITable */
            $databaseTableMetaData = $databaseDataSet->getTableMetaData($table->getTableMetaData()->getTableName());

            $insertQuery = $insertOperation->buildOperationQuery($databaseTableMetaData, $table, $connection);
            $updateQuery = $updateOperation->buildOperationQuery($databaseTableMetaData, $table, $connection);
            $selectQuery = $this->buildOperationQuery($databaseTableMetaData, $table, $connection);

            $insertStatement = $connection->getConnection()->prepare($insertQuery);
            $updateStatement = $connection->getConnection()->prepare($updateQuery);
            $selectStatement = $connection->getConnection()->prepare($selectQuery);

            for ($i = 0; $i < $table->getRowCount(); $i++) {
                $selectArgs = $this->buildOperationArguments($databaseTableMetaData, $table, $i);
                $query = $selectQuery;
                $args = $selectArgs;
                try {
                    $selectStatement->execute($selectArgs);

                    if ($selectStatement->fetchColumn(0) > 0) {
                        $updateArgs = $updateOperation->buildOperationArguments($databaseTableMetaData, $table, $i);
                        $query = $updateQuery;
                        $args = $updateArgs;
                        $updateStatement->execute($updateArgs);
                    } else {
                        $insertArgs = $insertOperation->buildOperationArguments($databaseTableMetaData, $table, $i);
                        $query = $insertQuery;
                        $args = $insertArgs;
                        $insertStatement->execute($insertArgs);
                    }
                } catch (Exception $e) {
                    throw new PHPUnit_Extensions_Database_Operation_Exception($this->operationName, $query, $args, $table, $e->getMessage());
                }
            }
        }
    }
}
