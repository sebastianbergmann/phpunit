<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

require_once 'PHPUnit/Extensions/Database/DataSet/AbstractDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/TableFilter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A dataset decorator that allows filtering out tables and table columns from
 * results.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2008 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Extensions_Database_DataSet_DataSetFilter extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{

    /**
     * The dataset being decorated.
     * @var PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected $originalDataSet;

    /**
     * The tables to exclude from the data set.
     * @var Array
     */
    protected $excludeTables;

    /**
     * The columns to exclude from the data set.
     * @var Array
     */
    protected $excludeColumns;

    /**
     * Creates a new filtered data set.
     *
     * The $exclude tables should be an associative array using table names as
     * the key and an array of column names to exclude for the value. If you
     * would like to exclude a full table set the value of the table's entry
     * to the special string '*'.
     *
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $originalDataSet
     * @param Array $excludeTables
     */
    public function __construct(PHPUnit_Extensions_Database_DataSet_IDataSet $originalDataSet, Array $excludeTables)
    {
        $this->originalDataSet = $originalDataSet;
        $this->excludeTables = $excludeTables;

        foreach ($this->excludeTables as $tableName => $values) {
            if (is_array($values)) {
                $this->excludeColumns[$tableName] = $values;
            } elseif ($values == '*') {
                $this->excludeTables[] = $tableName;
            } else {
                $this->excludeColumns[$tableName] = (array)$values;
            }
        }
    }

    /**
     * Creates an iterator over the tables in the data set. If $reverse is
     * true a reverse iterator will be returned.
     *
     * @param bool $reverse
     * @return PHPUnit_Extensions_Database_DataSet_ITableIterator
     */
    protected function createIterator($reverse = FALSE)
    {
        $original_tables = $this->originalDataSet->getIterator($reverse);
        $new_tables = array();

        foreach ($original_tables as $table) {
            /* @var $table PHPUnit_Extensions_Database_DataSet_ITable */
            $tableName = $table->getTableMetaData()->getTableName();

            if (in_array($tableName, $this->excludeTables)) {
                continue;
            } elseif (array_key_exists($tableName, $this->excludeColumns)) {
                $new_tables[] = new PHPUnit_Extensions_Database_DataSet_TableFilter($table, $this->excludeColumns[$tableName]);
            } else {
                $new_tables[] = $table;
            }
        }

        return new PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($new_tables);
    }
}
?>
