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
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';

require_once 'PHPUnit/Extensions/Database/DataSet/IPersistable.php';

PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');


/**
 * An abstract implementation of a dataset persistor.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
abstract class PHPUnit_Extensions_Database_DataSet_Persistors_Abstract implements PHPUnit_Extensions_Database_DataSet_IPersistable
{
    public function write(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset)
    {
        $this->saveDataSet($dataset);
    }

    /**
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
     */
    protected function saveDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset)
    {
        $this->startDataSet($dataset);
        foreach ($dataset as $table)
        {
            $this->saveTable($table);
        }
        $this->endDataSet($dataset);
    }

    /**
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     */
    protected function saveTable(PHPUnit_Extensions_Database_DataSet_ITable $table)
    {
        $this->startTable($table);
        for ($i = 0; $i < $table->getRowCount(); $i++)
        {
            $this->row($table->getRow($i), $table);
        }
        $this->endTable($table);
    }

    /**
     * Override to save the start of a dataset.
     *
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
     */
    abstract protected function startDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset);

    /**
     * Override to save the end of a dataset.
     *
     * @param PHPUnit_Extensions_Database_DataSet_IDataSet $dataset
     */
    abstract protected function endDataSet(PHPUnit_Extensions_Database_DataSet_IDataSet $dataset);

    /**
     * Override to save the start of a table.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     */
    abstract protected function startTable(PHPUnit_Extensions_Database_DataSet_ITable $table);

    /**
     * Override to save the end of a table.
     *
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     */
    abstract protected function endTable(PHPUnit_Extensions_Database_DataSet_ITable $table);

    /**
     * Override to save a table row.
     *
     * @param array $row
     * @param PHPUnit_Extensions_Database_DataSet_ITable $table
     */
    abstract protected function row(Array $row, PHPUnit_Extensions_Database_DataSet_ITable $table);
}

?>
