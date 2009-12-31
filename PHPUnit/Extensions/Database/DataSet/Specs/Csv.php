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
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

require_once ('PHPUnit/Extensions/Database/DataSet/ISpec.php');
require_once ('PHPUnit/Extensions/Database/DataSet/CsvDataSet.php');

/**
 * Creates CsvDataSets based off of a spec string.
 *
 * The format of the spec string is as follows:
 *
 * <csv options>|table1:filename.csv,table2:filename2.csv
 *
 * The first portion of the spec including the pipe symbol '|' is optional.
 * If the pipe option is included than it may be preceded by up to four
 * characters specifying values for the following arguments in order:
 * delimiter (defaults to ',',) enclosure (defaults to '"',) escape (defaults to '"',).
 *
 * Any additional characters in the csv options will be discarded.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Extensions_Database_DataSet_Specs_Csv implements PHPUnit_Extensions_Database_DataSet_ISpec
{

    /**
     * Creates CSV Data Set from a data set spec.
     *
     * @param string $dataSetSpec
     * @return PHPUnit_Extensions_Database_DataSet_CsvDataSet
     */
    public function getDataSet($dataSetSpec)
    {
        $csvDataSetArgs = $this->getCsvOptions($dataSetSpec);
        $csvDataSetRfl = new ReflectionClass('PHPUnit_Extensions_Database_DataSet_CsvDataSet');
        $csvDataSet = $csvDataSetRfl->newInstanceArgs($csvDataSetArgs);

        foreach ($this->getTableFileMap($dataSetSpec) as $tableName => $file) {
            $csvDataSet->addTable($tableName, $file);
        }
        return $csvDataSet;
    }

    /**
     * Returns CSV options.
     *
     * Returns an array containing the options that will be passed to the
     * PHPUnit_Extensions_Database_DataSet_CsvDataSet constructor. The options
     * are determined by the given $dataSetSpec.
     *
     * @param string $dataSetSpec
     * @return array
     */
    protected function getCsvOptions($dataSetSpec)
    {
        list($csvOptStr, ) = explode('|', $dataSetSpec, 2);
        return str_split($csvOptStr);
    }

    /**
     * Returns map of tables to files.
     *
     * Returns an associative array containing a mapping of tables (the key)
     * to files (the values.) The tables and files are determined by the given
     * $dataSetSpec
     *
     * @param string $dataSetSpec
     * @return array
     */
    protected function getTableFileMap($dataSetSpec)
    {
        $tables = array();
        foreach (explode(',', $dataSetSpec) as $csvfile) {
            list($tableName, $file) = explode(':', $csvfile, 2);
            $tables[$tableName] = $file;
        }

        return $tables;
    }
}

?>