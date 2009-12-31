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

require_once ('PHPUnit/Extensions/Database/UI/IMode.php');
require_once ('PHPUnit/Extensions/Database/UI/Modes/ExportDataSet/Arguments.php');
require_once ('PHPUnit/Extensions/Database/DataSet/CompositeDataSet.php');
require_once ('PHPUnit/Extensions/Database/DataSet/Specs/Factory.php');
require_once ('PHPUnit/Extensions/Database/DataSet/Persistors/Factory.php');

/**
 * The class for the export-dataset command.
 *
 * This command is used to convert existing data sets or data in the database
 * into a valid data set format.
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
class PHPUnit_Extensions_Database_UI_Modes_ExportDataSet implements PHPUnit_Extensions_Database_UI_IMode
{
    /**
     * Executes the export dataset command.
     *
     * @param array $modeArguments
     * @param PHPUnit_Extensions_Database_UI_IMediumPrinter $medium
     */
    public function execute(array $modeArguments, PHPUnit_Extensions_Database_UI_IMediumPrinter $medium)
    {
        $arguments = new PHPUnit_Extensions_Database_UI_Modes_ExportDataSet_Arguments($modeArguments);

        if (FALSE && !$arguments->areValid()) {
            throw new InvalidArgumentException("The arguments for this command are incorrect.");
        }

        $datasets = array();
        foreach ($arguments->getArgumentArray('dataset') as $argString) {
            $datasets[] = $this->getDataSetFromArgument($argString, $arguments->getDatabases());
        }

        $finalDataset = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet($datasets);

        $outputDataset = $this->getPersistorFromArgument($arguments->getSingleArgument('output'));
        $outputDataset->write($finalDataset);
    }

    /**
     * Returns the correct dataset given an argument containing a dataset spec.
     *
     * @param string $argString
     * @param array $databaseList
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSetFromArgument($argString, $databaseList)
    {
        $dataSetSpecFactory = new PHPUnit_Extensions_Database_DataSet_Specs_Factory();
        list($type, $dataSetSpecStr) = explode(':', $argString, 2);
        $dataSetSpec = $dataSetSpecFactory->getDataSetSpecByType($type);

        if ($dataSetSpec instanceof PHPUnit_Extensions_Database_IDatabaseListConsumer) {
            $dataSetSpec->setDatabases($databaseList);
        }

        return $dataSetSpec->getDataSet($dataSetSpecStr);
    }

    /**
     * Returns the correct persistor given an argument containing a persistor spec.
     *
     * @param string $argString
     * @return PHPUnit_Extensions_Database_DataSet_IPersistable
     */
    protected function getPersistorFromArgument($argString)
    {
        $persistorFactory = new PHPUnit_Extensions_Database_DataSet_Persistors_Factory();
        list($type, $spec) = explode(':', $argString, 2);
        return $persistorFactory->getPersistorBySpec($type, $spec);
    }
}

?>
