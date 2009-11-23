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

require_once 'PHPUnit/Extensions/Database/DataSet/AbstractTableMetaData.php';

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

/**
 * A TableMetaData decorator that allows filtering columns from another
 * metaData object.
 *
 * The if a whitelist (include) filter is specified, then only those columns
 * will be included.
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
class PHPUnit_Extensions_Database_DataSet_TableMetaDataFilter extends PHPUnit_Extensions_Database_DataSet_AbstractTableMetaData
{

    /**
     * The table meta data being decorated.
     * @var PHPUnit_Extensions_Database_DataSet_ITableMetaData
     */
    protected $originalMetaData;

    /**
     * The columns to exclude from the meta data.
     * @var Array
     */
    protected $excludeColumns = array();

    /**
     * The columns to include from the meta data.
     * @var Array
     */
    protected $includeColumns = array();

    /**
     * Creates a new filtered table meta data object filtering out
     * $excludeColumns.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITableMetaData $originalMetaData
     * @param array $excludeColumns - Deprecated. Use the set* methods instead.
     */
    public function __construct(PHPUnit_Extensions_Database_DataSet_ITableMetaData $originalMetaData, Array $excludeColumns = array())
    {
        $this->originalMetaData = $originalMetaData;
        $this->addExcludeColumns($excludeColumns);
    }

    /**
     * Returns the names of the columns in the table.
     *
     * @return array
     */
    public function getColumns()
    {
        if (!empty($this->includeColumns)) {
            return array_values(array_intersect($this->originalMetaData->getColumns(), $this->includeColumns));
        }
        elseif (!empty($this->excludeColumns)) {
            return array_values(array_diff($this->originalMetaData->getColumns(), $this->excludeColumns));
        }
        else {
            return $this->originalMetaData->getColumns();
        }
    }

    /**
     * Returns the names of the primary key columns in the table.
     *
     * @return array
     */
    public function getPrimaryKeys()
    {
        return $this->originalMetaData->getPrimaryKeys();
    }

    /**
     * Returns the name of the table.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->originalMetaData->getTableName();
    }

    /**
     * Sets the columns to include in the table.
     * @param Array $includeColumns
     */
    public function addIncludeColumns(Array $includeColumns)
    {
        $this->includeColumns = array_unique(array_merge($this->includeColumns, $includeColumns));
    }

    /**
     * Clears the included columns.
     */
    public function clearIncludeColumns()
    {
        $this->includeColumns = array();
    }

    /**
     * Sets the columns to exclude from the table.
     * @param Array $excludeColumns
     */
    public function addExcludeColumns(Array $excludeColumns)
    {
        $this->excludeColumns = array_unique(array_merge($this->excludeColumns, $excludeColumns));
    }

    /**
     * Clears the excluded columns.
     */
    public function clearExcludeColumns()
    {
        $this->excludeColumns = array();
    }
}
?>
