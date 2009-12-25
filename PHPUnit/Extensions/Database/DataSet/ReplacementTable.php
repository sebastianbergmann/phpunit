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
 * @since      File available since Release 3.3.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Extensions/Database/DataSet/ITable.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Allows for replacing arbitrary strings in your data sets with other values.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2009 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 * @todo When setTableMetaData() is taken out of the AbstractTable this class should extend AbstractTable.
 */
class PHPUnit_Extensions_Database_DataSet_ReplacementTable implements PHPUnit_Extensions_Database_DataSet_ITable
{
    /**
     * @var PHPUnit_Extensions_Database_DataSet_ITable
     */
    protected $table;

    /**
     * @var array
     */
    protected $fullReplacements;

    /**
     * @var array
     */
    protected $subStrReplacements;

    /**
     * Creates a new replacement table
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     * @param array $fullReplacements
     * @param array $subStrReplacements
     */
    public function __construct(PHPUnit_Extensions_Database_DataSet_ITable $table, Array $fullReplacements = array(), Array $subStrReplacements = array())
    {
        $this->table = $table;
        $this->fullReplacements = $fullReplacements;
        $this->subStrReplacements = $subStrReplacements;
    }

    /**
     * Adds a new full replacement
     *
     * Full replacements will only replace values if the FULL value is a match
     *
     * @param string $value
     * @param string $replacement
     */
    public function addFullReplacement($value, $replacement)
    {
        $this->fullReplacements[$value] = $replacement;
    }

    /**
     * Adds a new substr replacement
     *
     * Substr replacements will replace all occurances of the substr in every column
     *
     * @param string $value
     * @param string $replacement
     */
    public function addSubStrReplacement($value, $replacement)
    {
        $this->subStrReplacements[$value] = $replacement;
    }

    /**
     * Returns the table's meta data.
     *
     * @return PHPUnit_Extensions_Database_DataSet_ITableMetaData
     */
    public function getTableMetaData()
    {
        return $this->table->getTableMetaData();
    }

    /**
     * Returns the number of rows in this table.
     *
     * @return int
     */
    public function getRowCount()
    {
        return $this->table->getRowCount();
    }

    /**
     * Returns the value for the given column on the given row.
     *
     * @param int $row
     * @param int $column
     */
    public function getValue($row, $column)
    {
        return $this->getReplacedValue($this->table->getValue($row, $column));
    }

    /**
     * Returns the an associative array keyed by columns for the given row.
     *
     * @param int $row
     * @return array
     */
    public function getRow($row)
    {
        $row = $this->table->getRow($row);

        return array_map(array($this, 'getReplacedValue'), $row);
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

    protected function getReplacedValue($value)
    {
        if (is_scalar($value) && array_key_exists((string)$value, $this->fullReplacements))
        {
            return $this->fullReplacements[$value];
        }
        elseif (count($this->subStrReplacements))
        {
            return str_replace(array_keys($this->subStrReplacements), array_values($this->subStrReplacements), $value);
        }
        else
        {
            return $value;
        }
    }
}
?>
