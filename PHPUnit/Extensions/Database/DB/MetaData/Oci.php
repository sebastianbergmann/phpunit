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
 * @subpackage Extensions_Database_DB_MetaData
 * @author     Trond Hansen <trond@xait.no>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.3
 */

/**
 * Provides functionality to retrieve meta data from an Oracle database.
 *
 * @package    PHPUnit
 * @subpackage Extensions_Database_DB_MetaData
 * @author     Trond Hansen <trond@xait.no>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.3
 */
class PHPUnit_Extensions_Database_DB_MetaData_Oci extends PHPUnit_Extensions_Database_DB_MetaData
{
    /**
     * No character used to quote schema objects.
     */
    protected $schemaObjectQuoteChar = '';

    /**
     * The command used to perform a TRUNCATE operation.
     */
    protected $truncateCommand = 'TRUNCATE TABLE';

    protected $columns = array();
    protected $keys = array();

    /**
     * Returns an array containing the names of all the tables in the database.
     *
     * @return array
     */
    public function getTableNames()
    {
        $tableNames = array();

        $query = "SELECT table_name
                    FROM cat
                   WHERE table_type='TABLE'
                   ORDER BY table_name";

        $result = $this->pdo->query($query);

        while ($tableName = $result->fetchColumn(0)) {
            $tableNames[] = $tableName;
        }

        return $tableNames;
    }

    /**
     * Returns an array containing the names of all the columns in the
     * $tableName table,
     *
     * @param string $tableName
     * @return array
     */
    public function getTableColumns($tableName)
    {
        if (!isset($this->columns[$tableName])) {
            $this->loadColumnInfo($tableName);
        }

        return $this->columns[$tableName];
   }

    /**
     * Returns an array containing the names of all the primary key columns in
     * the $tableName table.
     *
     * @param string $tableName
     * @return array
     */
    public function getTablePrimaryKeys($tableName)
    {
        if (!isset($this->keys[$tableName])) {
            $this->loadColumnInfo($tableName);
        }

        return $this->keys[$tableName];
    }

    /**
     * Loads column info from a oracle database.
     *
     * @param string $tableName
     */
    protected function loadColumnInfo($tableName)
    {
        $ownerQuery    = '';
        $conOwnerQuery = '';
        $tableParts    = $this->splitTableName($tableName);

        $this->columns[$tableName] = array();
        $this->keys[$tableName]    = array();

        if (!empty($tableParts['schema']))
        {
            $ownerQuery = " AND OWNER = '{$tableParts['schema']}'";
            $conOwnerQuery = " AND a.owner = '{$tableParts['schema']}'";
        }

        $query = "SELECT DISTINCT COLUMN_NAME
                    FROM USER_TAB_COLUMNS
                   WHERE TABLE_NAME='".$tableParts['table']."'
                    $ownerQuery
                   ORDER BY COLUMN_NAME";

        $result = $this->pdo->query($query);

        while ($columnName = $result->fetchColumn(0)) {
            $this->columns[$tableName][] = $columnName;
        }

        $keyQuery = "SELECT b.column_name
                       FROM user_constraints a, user_cons_columns b
                      WHERE a.constraint_type='P'
                        AND a.constraint_name=b.constraint_name
                        $conOwnerQuery
                        AND a.table_name = '".$tableParts['table']."' ";

        $result = $this->pdo->query($keyQuery);

        while ($columnName = $result->fetchColumn(0)) {
            $this->keys[$tableName][] = $columnName;
        }
    }
}
