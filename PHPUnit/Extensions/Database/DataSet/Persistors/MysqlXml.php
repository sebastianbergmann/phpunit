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
 * @subpackage Extensions_Database_DataSet_Persistors
 * @author     Matthew Turland <tobias382@gmail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.0
 */

/**
 * A MySQL XML dataset persistor.
 *
 * @package    PHPUnit
 * @subpackage Extensions_Database_DataSet_Persistors
 * @author     Matthew Turland <tobias382@gmail.com>
 * @copyright  2010 Matthew Turland <tobias382@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.0
 */
class PHPUnit_Extensions_Database_DataSet_Persistors_MysqlXml extends PHPUnit_Extensions_Database_DataSet_Persistors_Abstract
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var resource
     */
    protected $fh;

    /**
     * Sets the filename that this persistor will save to.
     *
     * @param string $filename
     */
    public function setFileName($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Sets the name of the database.
     *
     * @param string $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * Override to save the start of a dataset.
     *
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
     */
    protected function startDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset)
    {
        $this->fh = fopen($this->filename, 'w');

        if ($this->fh === FALSE) {
           throw new PHPUnit_Framework_Exception("Could not open {$this->filename} for writing see " . __CLASS__ . "::setFileName()");
        }

        fwrite($this->fh, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        fwrite($this->fh, '<mysqldump xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . "\n");
        fwrite($this->fh, '<database name="' . $this->database . '">' . "\n");
    }

    /**
     * Override to save the end of a dataset.
     *
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
     */
    protected function endDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset)
    {
        fwrite($this->fh, '</database>' . "\n");
        fwrite($this->fh, '</mysqldump>' . "\n");
    }

    /**
     * Override to save the start of a table.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     */
    protected function startTable(PHPUnit_Extensions_Database_DataSet_ITable $table)
    {
        fwrite($this->fh, "\t" . '<table_data name="' . $table->getTableMetaData()->getTableName() . '">' . "\n");
    }

    /**
     * Override to save the end of a table.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     */
    protected function endTable(PHPUnit_Extensions_Database_DataSet_ITable $table)
    {
        fwrite($this->fh, "\t" . '</table_data>' . "\n");
    }

    /**
     * Override to save a table row.
     *
     * @param array $row
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     */
    protected function row(Array $row, PHPUnit_Extensions_Database_DataSet_ITable $table)
    {
        fwrite($this->fh, "\t" . '<row>' . "\n");

        foreach ($table->getTableMetaData()->getColumns() as $columnName) {
            fwrite($this->fh, "\t\t" . '<field name="' . $columnName . '"');
            if (isset($row[$columnName])) {
                fwrite($this->fh, '>' . htmlspecialchars($row[$columnName]) . '</field>' . "\n");
            } else {
                fwrite($this->fh, ' xsi:nil="true" />' . "\n");
            }
        }

        fwrite($this->fh, "\t" . '</row>' . "\n");
    }
}
