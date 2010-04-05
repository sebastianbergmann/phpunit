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
 * @subpackage Extensions_Database_DataSet
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

/**
 * Provides a basic functionality for dbunit tables
 *
 * @package    PHPUnit
 * @subpackage Extensions_Database_DataSet
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Extensions_Database_DataSet_AbstractTable implements PHPUnit_Extensions_Database_DataSet_ITable
{

    /**
     * @var PHPUnit_Extensions_Database_DataSet_ITableMetaData
     */
    protected $tableMetaData;

    /**
     * A 2-dimensional array containing the data for this table.
     *
     * @var array
     */
    protected $data;

    /**
     * Sets the metadata for this table.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData
     * @deprecated
     */
    protected function setTableMetaData(PHPUnit_Extensions_Database_DataSet_ITableMetaData $tableMetaData)
    {
        $this->tableMetaData = $tableMetaData;
    }

    /**
     * Returns the table's meta data.
     *
     * @return PHPUnit_Extensions_Database_DataSet_ITableMetaData
     */
    public function getTableMetaData()
    {
        return $this->tableMetaData;
    }

    /**
     * Returns the number of rows in this table.
     *
     * @return int
     */
    public function getRowCount()
    {
        return count($this->data);
    }

    /**
     * Returns the value for the given column on the given row.
     *
     * @param int $row
     * @param int $column
     * @todo reorganize this function to throw the exception first.
     */
    public function getValue($row, $column)
    {
        if (isset($this->data[$row][$column])) {
            return (string)$this->data[$row][$column];
        } else {
            if (!in_array($column, $this->getTableMetaData()->getColumns()) || $this->getRowCount() <= $row) {
                throw new InvalidArgumentException("The given row ({$row}) and column ({$column}) do not exist in table {$this->getTableMetaData()->getTableName()}");
            } else {
                return NULL;
            }
        }
    }

    /**
     * Returns the an associative array keyed by columns for the given row.
     *
     * @param int $row
     * @return array
     */
    public function getRow($row)
    {
        if (isset($this->data[$row])) {
            return $this->data[$row];
        } else {
            if ($this->getRowCount() <= $row) {
                throw new InvalidArgumentException("The given row ({$row}) does not exist in table {$this->getTableMetaData()->getTableName()}");
            } else {
                return NULL;
            }
        }
    }

    /**
     * Asserts that the given table matches this table.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITable $other
     */
    public function assertEquals(PHPUnit_Extensions_Database_DataSet_ITable $other)
    {
        $thisMetaData = $this->getTableMetaData();
        $otherMetaData = $other->getTableMetaData();

        $thisMetaData->assertEquals($otherMetaData);

        if ($this->getRowCount() != $other->getRowCount()) {
            throw new Exception("Expected row count of {$this->getRowCount()}, has a row count of {$other->getRowCount()}");
        }

        $columns = $thisMetaData->getColumns();
        for ($i = 0; $i < $this->getRowCount(); $i++) {
            foreach ($columns as $columnName) {
                if ($this->getValue($i, $columnName) != $other->getValue($i, $columnName)) {
                    throw new Exception("Expected value of {$this->getValue($i, $columnName)} for row {$i} column {$columnName}, has a value of {$other->getValue($i, $columnName)}");
                }
            }
        }

        return TRUE;
    }

    public function __toString()
    {
        $columns = $this->getTableMetaData()->getColumns();

        $lineSeperator = str_repeat('+----------------------', count($columns)) . "+\n";
        $lineLength = strlen($lineSeperator) - 1;

        $tableString = $lineSeperator;
        $tableString .= '| ' . str_pad($this->getTableMetaData()->getTableName(), $lineLength - 4, ' ', STR_PAD_RIGHT) . " |\n";
        $tableString .= $lineSeperator;
        $tableString .= $this->rowToString($columns);
        $tableString .= $lineSeperator;

        for ($i = 0; $i < $this->getRowCount(); $i++) {
            $values = array();
            foreach ($columns as $columnName) {
                $values[] = $this->getValue($i, $columnName);
            }

            $tableString .= $this->rowToString($values);
            $tableString .= $lineSeperator;
        }

        return "\n" . $tableString . "\n";
    }

    protected function rowToString(Array $row)
    {
        $rowString = '';
        foreach ($row as $value) {
            if (is_null($value)) {
                $value = 'NULL';
            }
            $rowString .= '| ' . str_pad(substr($value, 0, 20), 20, ' ', STR_PAD_BOTH) . ' ';
        }

        return $rowString . "|\n";
    }
}
