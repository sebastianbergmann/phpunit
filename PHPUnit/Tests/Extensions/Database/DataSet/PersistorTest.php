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

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/FlatXmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/XmlDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTable.php';
require_once 'PHPUnit/Extensions/Database/DataSet/DefaultTableMetaData.php';

/**
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */
class Extensions_Database_DataSet_PersistorTest extends PHPUnit_Framework_TestCase
{
    public function testFlatXml()
    {
        $dataSetFile = dirname(__FILE__).'/../_files/XmlDataSets/FlatXmlWriter.xml';
        $filename    = dirname(__FILE__).'/'.uniqid().'.xml';
        $dataSet     = new PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet($dataSetFile);

        PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet::write($dataSet, $filename);
        $this->assertXmlFileEqualsXmlFile($dataSetFile, $filename);
        unlink($filename);
    }

    public function testXml()
    {
        $dataSetFile = dirname(__FILE__).'/../_files/XmlDataSets/XmlWriter.xml';
        $filename    = dirname(__FILE__).'/'.uniqid().'.xml';
        $dataSet     = new PHPUnit_Extensions_Database_DataSet_XmlDataSet($dataSetFile);

        PHPUnit_Extensions_Database_DataSet_XmlDataSet::write($dataSet, $filename);
        $this->assertXmlFileEqualsXmlFile($dataSetFile, $filename);
        unlink($filename);
    }

    public function testEntitiesFlatXml()
    {
        $metaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table1', array('col1', 'col2'), array('col1'));
        $table    = new PHPUnit_Extensions_Database_DataSet_DefaultTable($metaData);
        $table->addRow(array('col1' => 1, 'col2' => '<?xml version="1.0"?><myxml>test</myxml>'));
        $dataSet  = new PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($table));

        $expectedFile = dirname(__FILE__).'/../_files/XmlDataSets/FlatXmlWriterEntities.xml';
        $filename     = dirname(__FILE__).'/'.uniqid().'.xml';
        PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet::write($dataSet, $filename);
        $this->assertXmlFileEqualsXmlFile($expectedFile, $filename);
        unlink($filename);
    }

    public function testEntitiesXml()
    {
        $metaData = new PHPUnit_Extensions_Database_DataSet_DefaultTableMetaData('table1', array('col1', 'col2'), array('col1'));
        $table = new PHPUnit_Extensions_Database_DataSet_DefaultTable($metaData);
        $table->addRow(array('col1' => 1, 'col2' => '<?xml version="1.0"?><myxml>test</myxml>'));
        $dataSet = new PHPUnit_Extensions_Database_DataSet_DefaultDataSet(array($table));

        $expectedFile = dirname(__FILE__).'/../_files/XmlDataSets/XmlWriterEntities.xml';
        $filename = dirname(__FILE__).'/'.uniqid().'.xml';
        PHPUnit_Extensions_Database_DataSet_XmlDataSet::write($dataSet, $filename);
        $this->assertXmlFileEqualsXmlFile($expectedFile, $filename);
        unlink($filename);
    }
}
?>
