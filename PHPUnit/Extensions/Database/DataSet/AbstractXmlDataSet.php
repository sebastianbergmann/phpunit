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
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

require_once 'PHPUnit/Extensions/Database/DataSet/AbstractDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableIterator.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableMetaData.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTable.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * The default implementation of a data set.
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
abstract class PHPUnit_Extensions_Database_DataSet_AbstractXmlDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{

    /**
     * @var array
     */
    protected $tables;

    /**
     * @var SimpleXmlElement
     */
    protected $xmlFileContents;

    /**
     * Creates a new dataset using the given tables.
     *
     * @param array $tables
     */
    public function __construct($xmlFile)
    {
        if (!is_file($xmlFile)) {
            throw new InvalidArgumentException("Could not find xml file: {$xmlFile}");
        }
        $this->xmlFileContents = @simplexml_load_file($xmlFile);

        if ($this->xmlFileContents === FALSE) {
            throw new InvalidArgumentException("File is not valid xml: {$xmlFile}");
        }

        $tableColumns = array();
        $tableValues = array();

        $this->getTableInfo($tableColumns, $tableValues);
        $this->createTables($tableColumns, $tableValues);
    }

    /**
     * Reads the simple xml object and creates the appropriate tables and meta
     * data for this dataset.
     */
    protected abstract function getTableInfo(Array &$tableColumns, Array &$tableValues);

    protected function createTables(Array &$tableColumns, Array &$tableValues)
    {
        foreach ($tableValues as $tableName => $values) {
            $table = $this->getOrCreateTable($tableName, $tableColumns[$tableName]);
            foreach ($values as $value) {
                $table->addRow($value);
            }
        }
    }

    /**
     * Returns the table with the matching name. If the table does not exist
     * an empty one is created.
     *
     * @param string $tableName
     * @return PHPUnit_Extensions_Database_DataSet_ITable
     */
    protected function getOrCreateTable($tableName, $tableColumns)
    {
        if (empty($this->tables[$tableName])) {
            $tableMetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $tableColumns);
            $this->tables[$tableName] = new PHPUnit_Extensions_Database_DataSet_DefaultTable($tableMetaData);
        }

        return $this->tables[$tableName];
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
        return new PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
    }
}
?>
