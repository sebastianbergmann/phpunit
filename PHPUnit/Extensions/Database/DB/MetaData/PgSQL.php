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
 * @version    SVN: $Id:InformationSchema.php 1254 2009-09-02 04:36:15Z mlively $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Extensions/Database/DB/MetaData/InformationSchema.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Provides functionality to retrieve meta data from a postgres database.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Extensions_Database_DB_MetaData_PgSQL extends PHPUnit_Extensions_Database_DB_MetaData_InformationSchema
{

    /**
     * Returns an array containing the names of all the tables in the database.
     *
     * @return array
     */
    public function getTableNames()
    {
        $query = "
            SELECT DISTINCT
            	TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE
                TABLE_TYPE='BASE TABLE' AND
                TABLE_CATALOG = ? AND
                TABLE_SCHEMA = 'public'
            ORDER BY TABLE_NAME
        ";

        $statement = $this->pdo->prepare($query);
        $statement->execute(array($this->getSchema()));

        $tableNames = array();
        while ($tableName = $statement->fetchColumn(0)) {
            $tableNames[] = $tableName;
        }

        return $tableNames;
    }

    /**
     * Loads column info from a sqlite database.
     *
     * @param string $tableName
     */
    protected function loadColumnInfo($tableName)
    {
        $this->columns[$tableName] = array();
        $this->keys[$tableName] = array();

        $columnQuery = "
            SELECT DISTINCT
            	COLUMN_NAME,
		ORDINAL_POSITION
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_NAME = ? AND
                TABLE_SCHEMA = 'public' AND
                TABLE_CATALOG = ?
            ORDER BY ORDINAL_POSITION
        ";

        $columnStatement = $this->pdo->prepare($columnQuery);
        $columnStatement->execute(array($tableName, $this->getSchema()));

        while ($columName = $columnStatement->fetchColumn(0)) {
            $this->columns[$tableName][] = $columName;
        }

        $keyQuery = "
			SELECT
				KCU.COLUMN_NAME,
				KCU.ORDINAL_POSITION
			FROM
				INFORMATION_SCHEMA.TABLE_CONSTRAINTS as TC,
				INFORMATION_SCHEMA.KEY_COLUMN_USAGE as KCU
			WHERE
				TC.CONSTRAINT_NAME = KCU.CONSTRAINT_NAME AND
				TC.TABLE_NAME = KCU.TABLE_NAME AND
				TC.TABLE_SCHEMA = KCU.TABLE_SCHEMA AND
                                TC.TABLE_CATALOG = KCU.TABLE_CATALOG AND
				TC.CONSTRAINT_TYPE = 'PRIMARY KEY' AND
				TC.TABLE_NAME = ? AND
				TC.TABLE_SCHEMA = 'public' AND
                                TC.TABLE_CATALOG = ?
			ORDER BY
				KCU.ORDINAL_POSITION ASC
    	";

        $keyStatement = $this->pdo->prepare($keyQuery);
        $keyStatement->execute(array($tableName, $this->getSchema()));

        while ($columName = $keyStatement->fetchColumn(0)) {
            $this->keys[$tableName][] = $columName;
        }
    }

    /**
     * Returns true if the rdbms allows cascading
     *
     * @return bool
     */
    public function allowsCascading()
    {
        return TRUE;
    }
}
?>
