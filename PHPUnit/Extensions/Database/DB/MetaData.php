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
 * @version    SVN: $Id:MetaData.php 1254 2009-09-02 04:36:15Z mlively $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Extensions/Database/DB/IMetaData.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Provides a basic constructor for all meta data classes and a factory for
 * generating the appropriate meta data class.
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
abstract class PHPUnit_Extensions_Database_DB_MetaData implements PHPUnit_Extensions_Database_DB_IMetaData
{
    protected static $metaDataClassMap = array(
        'pgsql'  => 'PHPUnit_Extensions_Database_DB_MetaData_PgSQL',
        'mysql'  => 'PHPUnit_Extensions_Database_DB_MetaData_MySQL',
        'oci'    => 'PHPUnit_Extensions_Database_DB_MetaData_Oci',
        'sqlite' => 'PHPUnit_Extensions_Database_DB_MetaData_Sqlite'
    );

    /**
     * The PDO connection used to retreive database meta data
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The default schema name for the meta data object.
     *
     * @var string
     */
    protected $schema;

    /**
     * The character used to quote schema objects.
     */
    protected $schemaObjectQuoteChar = '"';

    /**
     * The command used to perform a TRUNCATE operation.
     */
    protected $truncateCommand = 'TRUNCATE';

    /**
     * Creates a new database meta data object using the given pdo connection
     * and schema name.
     *
     * @param PDO $pdo
     * @param string $schema
     */
    public final function __construct(PDO $pdo, $schema)
    {
        $this->pdo = $pdo;
        $this->schema = $schema;
    }

    /**
     * Creates a meta data object based on the driver of given $pdo object and
     * $schema name.
     *
     * @param PDO $pdo
     * @param string $schema
     * @return PHPUnit_Extensions_Database_DB_MetaData
     */
    public static function createMetaData(PDO $pdo, $schema)
    {
        $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if (isset(self::$metaDataClassMap[$driverName])) {
            $className = self::$metaDataClassMap[$driverName];

            if ($className instanceof ReflectionClass) {
                return $className->newInstance($pdo, $schema);
            } else {
                return self::registerClassWithDriver($className, $driverName)->newInstance($pdo, $schema);
            }
        } else {
            throw new Exception("Could not find a meta data driver for {$driverName} pdo driver.");
        }
    }

    /**
     * Validates and registers the given $className with the given $pdoDriver.
     * It should be noted that this function will not attempt to include /
     * require the file. The $pdoDriver can be determined by the value of the
     * PDO::ATTR_DRIVER_NAME attribute for a pdo object.
     *
     * A reflection of the $className is returned.
     *
     * @param string $className
     * @param string $pdoDriver
     * @return ReflectionClass
     */
    public static function registerClassWithDriver($className, $pdoDriver)
    {
        if (!class_exists($className)) {
            throw new Exception("Specified class for {$pdoDriver} driver ({$className}) does not exist.");
        }

        $reflection = new ReflectionClass($className);
        if ($reflection->isSubclassOf('PHPUnit_Extensions_Database_DB_MetaData')) {
            return self::$metaDataClassMap[$pdoDriver] = $reflection;
        } else {
            throw new Exception("Specified class for {$pdoDriver} driver ({$className}) does not extend PHPUnit_Extensions_Database_DB_MetaData.");
        }
    }

    /**
     * Returns the schema for the connection.
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Returns a quoted schema object. (table name, column name, etc)
     *
     * @param string $object
     * @return string
     */
    public function quoteSchemaObject($object)
    {
        return $this->schemaObjectQuoteChar.
        str_replace($this->schemaObjectQuoteChar, $this->schemaObjectQuoteChar.$this->schemaObjectQuoteChar, $object).
        $this->schemaObjectQuoteChar;
    }

    /**
     * Returns the command for the database to truncate a table.
     *
     * @return string
     */
    public function getTruncateCommand()
    {
        return $this->truncateCommand;
    }

    /**
     * Returns true if the rdbms allows cascading
     *
     * @return bool
     */
    public function allowsCascading()
    {
        return FALSE;
    }
}

/**
 * I am not sure why these requires can't go above the class, but when they do
 * the classes can't find the PHPUnit_Extensions_Database_DB_MetaData
 * class.
 */
require_once 'PHPUnit/Extensions/Database/DB/MetaData/Sqlite.php';
require_once 'PHPUnit/Extensions/Database/DB/MetaData/InformationSchema.php';
require_once 'PHPUnit/Extensions/Database/DB/MetaData/MySQL.php';
require_once 'PHPUnit/Extensions/Database/DB/MetaData/PgSQL.php';
?>
