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
 * @author     Matthew Turland <tobias382@gmail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.0
 */

require_once 'PHPUnit/Framework.php';

require_once 'PHPUnit/Extensions/Database/DataSet/AbstractXmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/Persistors/MysqlXml.php';

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

/**
 * Data set implementation for the output of mysqldump --xml -t.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Matthew Turland <tobias382@gmail.com>
 * @copyright  2010 Matthew Turland <tobias382@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.0
 */
class PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet extends PHPUnit_Extensions_Database_DataSet_AbstractXmlDataSet
{

    protected function getTableInfo(Array &$tableColumns, Array &$tableValues)
    {
        if ($this->xmlFileContents->getName() != 'mysqldump') {
            throw new Exception('The root element of a MySQL XML data set file must be called <mysqldump>');
        }

        foreach ($this->xmlFileContents->xpath('./database/table_data') as $tableElement) {
            if (empty($tableElement['name'])) {
                throw new Exception('<table_data> elements must include a name attribute');
            }

            $tableName = (string)$tableElement['name'];

            if (!isset($tableColumns[$tableName])) {
                $tableColumns[$tableName] = array();
            }

            if (!isset($tableValues[$tableName])) {
                $tableValues[$tableName] = array();
            }

            foreach ($tableElement->xpath('./row/field') as $columnElement) {
                $columnName = (string)$columnElement['name'];
                if (empty($columnName)) {
                    throw new Exception('<field> element name attributes cannot be empty');
                }

                if (!in_array($columnName, $tableColumns[$tableName])) {
                    $tableColumns[$tableName][] = $columnName;
                }
            }

            foreach ($this->xmlFileContents->xpath('./database/table_data[@name="' . $tableName . '"]/row') as $rowElement) {
                $rowValues = array();
                foreach ($tableColumns[$tableName] as $columnName) {
                    $fields = $rowElement->xpath('./field[@name="' . $columnName . '"]');
                    $column = array_shift($fields);
                    $null = isset($column['nil']);
                    $columnValue = $null ? NULL : (string)$column;
                    $rowValues[$columnName] = $columnValue;
                }
                $tableValues[$tableName][] = $rowValues;
            }
        }
    }

    public static function write(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset, $filename)
    {
        $pers = new PHPUnit_Extensions_Database_DataSet_Persistors_MysqlXml();
        $pers->setFileName($filename);

        try {
            $pers->write($dataset);
        } catch (RuntimeException $e) {
            throw new PHPUnit_Framework_Exception(__METHOD__ . ' called with an unwritable file.');
        }
    }
}
?>
