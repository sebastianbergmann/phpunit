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

require_once 'PHPUnit/Extensions/Database/DataSet/AbstractDataSet.php';

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

/**
 * Creates Composite Datasets
 *
 * Allows for creating datasets from multiple sources (csv, query, xml, etc.)
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2009 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.3.0
 */
class PHPUnit_Extensions_Database_DataSet_CompositeDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
    protected $dataSets = array();

    /**
     * Creates a new Composite dataset
     *
     * You can pass in any data set that implements PHPUnit_Extensions_Database_DataSet_IDataSet
     *
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */
    public function __construct(Array $dataSets)
    {
        foreach ($dataSets as $dataSet)
        {
            $this->addDataSet($dataSet);
        }
    }

    /**
     * Adds a new data set to the composite.
     *
     * The dataset may not define tables that already exist in the composite.
     *
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     */
    public function addDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
    {
        foreach ($dataSet->getTableNames() as $tableName)
        {
            if (in_array($tableName, $this->getTableNames()))
            {
                throw new InvalidArgumentException("DataSet contains a table that already exists: {$tableName}");
            }
        }

        $this->dataSets[] = $dataSet;
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
        $iterator = new AppendIterator();

        $dataSets = $reverse ? array_reverse($this->dataSets) : $this->dataSets;

        foreach ($dataSets as $dataSet)
        {
            /* @var $dataSet PHPUnit_Extensions_Database_DataSet_IDataSet */
            $dataSetIterator = $reverse ? $dataSet->getReverseIterator() : $dataSet->getIterator();
            $iterator->append($dataSetIterator);
        }
        return $iterator;
    }
}
?>
