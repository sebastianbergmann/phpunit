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
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableIterator.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTable.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableMetaData.php';
require_once 'PHPUnit/Extensions/Database/DataSet/Persistors/Yaml.php';
require_once 'SymfonyComponents/YAML/sfYaml.php';

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

/**
 * Creates CsvDataSets.
 *
 * You can incrementally add CSV files as tables to your datasets
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
class PHPUnit_Extensions_Database_DataSet_YamlDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractDataSet
{
    /**
     * @var array
     */
    protected $tables = array();

    /**
     * Creates a new YAML dataset
     *
     * @param string $yamlFile
     * @param string $enclosure
     * @param string $escape
     */
    public function __construct($yamlFile)
    {
        $this->addYamlFile($yamlFile);
    }

    /**
     * Adds a new yaml file to the dataset.
     * @param string $yamlFile
     */
    public function addYamlFile($yamlFile)
    {
        $data = sfYaml::load($yamlFile);

        foreach ($data as $tableName => $rows)
        {
            if (!is_array($rows)) {
                continue;
            }

            if (!array_key_exists($tableName, $this->tables))
            {
                $columns = count($rows) ? array_keys(current($rows)) : array();

                $tableMetaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData($tableName, $columns);

                $this->tables[$tableName] = new PHPUnit_Extensions_Database_DataSet_DefaultTable($tableMetaData);
            }

            foreach ($rows as $row)
            {
                $this->tables[$tableName]->addRow($row);
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
        return new PHPUnit_Extensions_Database_DataSet_DefaultTableIterator($this->tables, $reverse);
    }

    /**
     * Saves a given $dataset to $filename in YAML format
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
     * @param string $filename
     */
    public static function write(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset, $filename)
    {
        $pers = new PHPUnit_Extensions_Database_DataSet_Persistors_Yaml();
        $pers->setFileName($filename);

        try {
            $pers->write($dataset);
        } catch (RuntimeException $e) {
            throw new PHPUnit_Framework_Exception(__METHOD__ . ' called with an unwritable file.');
        }
    }
}
?>
