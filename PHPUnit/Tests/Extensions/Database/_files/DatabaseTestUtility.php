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
 * @since      File available since Release 4.0.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';
PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 4.0.0
 */
class DBUnitTestUtility
{
    protected static $connection;

    public static function getSQLiteMemoryDB()
    {
        if (self::$connection === NULL) {
            self::$connection = new PDO('sqlite::memory:');
            self::setUpDatabase(self::$connection);
        }

        return self::$connection;
    }

    /**
     * Creates connection to test MySQL database
     *
     * MySQL server must be installed locally, with root access
     * and empty password and listening on unix socket
     *
     * @return PDO
     * @see    DBUnitTestUtility::setUpMySqlDatabase()
     */
    public static function getMySQLDB()
    {
        if (self::$connection === NULL) {
            $connection = new PDO(
              'mysql:dbname=test;unix_socket=/tmp/mysql.sock'
            );

            self::setUpMySQLDatabase($connection);
        }

        return self::$connection;
    }

    protected static function setUpDatabase(PDO $connection)
    {
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $connection->exec(
          'CREATE TABLE table1 (
            table1_id INTEGER PRIMARY KEY AUTOINCREMENT,
            column1 VARCHAR(20),
            column2 INT(10),
            column3 DECIMAL(6,2),
            column4 TEXT
          )'
        );

        $connection->exec(
          'CREATE TABLE table2 (
            table2_id INTEGER PRIMARY KEY AUTOINCREMENT,
            column5 VARCHAR(20),
            column6 INT(10),
            column7 DECIMAL(6,2),
            column8 TEXT
          )'
        );

        $connection->exec(
          'CREATE TABLE table3 (
            table3_id INTEGER PRIMARY KEY AUTOINCREMENT,
            column9 VARCHAR(20),
            column10 INT(10),
            column11 DECIMAL(6,2),
            column12 TEXT
          )'
        );
    }

    /**
     * Creates default testing schema for MySQL database
     *
     * Tables must containt foreign keys and use InnoDb storage engine
     * for constraint tests to be executed properly
     *
     * @param PDO $connection PDO instance representing connection to MySQL database
     * @see   DBUnitTestUtility::getMySQLDB()
     */
    protected static function setUpMySqlDatabase(PDO $connection)
    {
        $connection->exec(
          'CREATE TABLE IF NOT EXISTS table1 (
            table1_id INTEGER AUTO_INCREMENT,
            column1 VARCHAR(20),
            column2 INT(10),
            column3 DECIMAL(6,2),
            column4 TEXT,
            PRIMARY KEY (table1_id)
          ) ENGINE=INNODB;
        ');

        $connection->exec(
          'CREATE TABLE IF NOT EXISTS table2 (
            table2_id INTEGER AUTO_INCREMENT,
            table1_id INTEGER,
            column5 VARCHAR(20),
            column6 INT(10),
            column7 DECIMAL(6,2),
            column8 TEXT,
            PRIMARY KEY (table2_id),
            FOREIGN KEY (table1_id) REFERENCES table1(table1_id)
          ) ENGINE=INNODB;
        ');

        $connection->exec(
          'CREATE TABLE IF NOT EXISTS table3 (
            table3_id INTEGER AUTO_INCREMENT,
            table2_id INTEGER,
            column9 VARCHAR(20),
            column10 INT(10),
            column11 DECIMAL(6,2),
            column12 TEXT,
            PRIMARY KEY (table3_id),
            FOREIGN KEY (table2_id) REFERENCES table2(table2_id)
          ) ENGINE=INNODB;
        ');
    }
}
?>