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
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';

require_once 'PHPUnit/Extensions/Database/DataSet/ITableIterator.php';

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

/**
 * Provides iterative access to tables from a database instance.
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
class PHPUnit_Extensions_Database_DB_TableIterator implements PHPUnit_Extensions_Database_DataSet_ITableIterator
{

    /**
     * An array of tablenames.
     *
     * @var Array
     */
    protected $tableNames;

    /**
     * If this property is true then the tables will be iterated in reverse
     * order.
     *
     * @var bool
     */
    protected $reverse;

    /**
     * The database dataset that this iterator iterates over.
     *
     * @var PHPUnit_Extensions_Database_DB_DataSet
     */
    protected $dataSet;

    public function __construct($tableNames, PHPUnit_Extensions_Database_DB_DataSet $dataSet, $reverse = FALSE)
    {
        $this->tableNames = $tableNames;
        $this->dataSet = $dataSet;
        $this->reverse = $reverse;

        $this->rewind();
    }

    /**
     * Returns the current table.
     *
     * @return PHPUnit_Extensions_Database_DataSet_ITable
     */
    public function getTable()
    {
        return $this->current();
    }

    /**
     * Returns the current table's meta data.
     *
     * @return PHPUnit_Extensions_Database_DataSet_ITableMetaData
     */
    public function getTableMetaData()
    {
        return $this->current()->getTableMetaData();
    }

    /**
     * Returns the current table.
     *
     * @return PHPUnit_Extensions_Database_DataSet_ITable
     */
    public function current()
    {
        $tableName = current($this->tableNames);
        return $this->dataSet->getTable($tableName);
    }

    /**
     * Returns the name of the current table.
     *
     * @return string
     */
    public function key()
    {
        return $this->current()->getTableMetaData()->getTableName();
    }

    /**
     * advances to the next element.
     *
     */
    public function next()
    {
        if ($this->reverse) {
            prev($this->tableNames);
        } else {
            next($this->tableNames);
        }
    }

    /**
     * Rewinds to the first element
     */
    public function rewind()
    {
        if ($this->reverse) {
            end($this->tableNames);
        } else {
            reset($this->tableNames);
        }
    }

    /**
     * Returns true if the current index is valid
     *
     * @return bool
     */
    public function valid()
    {
        return (current($this->tableNames) !== FALSE);
    }
}
?>
