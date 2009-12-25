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

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

require_once 'PHPUnit/Extensions/Database/Operation/IDatabaseOperation.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

require_once 'PHPUnit/Extensions/Database/Operation/Composite.php';
require_once 'PHPUnit/Extensions/Database/Operation/DeleteAll.php';
require_once 'PHPUnit/Extensions/Database/Operation/Delete.php';
require_once 'PHPUnit/Extensions/Database/Operation/Insert.php';
require_once 'PHPUnit/Extensions/Database/Operation/Null.php';
require_once 'PHPUnit/Extensions/Database/Operation/Update.php';
require_once 'PHPUnit/Extensions/Database/Operation/Truncate.php';

/**
 * A class factory to easily return database operations.
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
class PHPUnit_Extensions_Database_Operation_Factory
{

    /**
     * Returns a null database operation
     *
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    public static function NONE()
    {
        return new PHPUnit_Extensions_Database_Operation_Null();
    }

    /**
     * Returns a clean insert database operation. It will remove all contents
     * from the table prior to re-inserting rows.
     *
     * @param bool $cascadeTruncates Set to true to force truncates to cascade on databases that support this.
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    public static function CLEAN_INSERT($cascadeTruncates = FALSE)
    {
        return new PHPUnit_Extensions_Database_Operation_Composite(array(
            self::TRUNCATE($cascadeTruncates),
            self::INSERT()
        ));
    }

    /**
     * Returns an insert database operation.
     *
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    public static function INSERT()
    {
        return new PHPUnit_Extensions_Database_Operation_Insert();
    }

    /**
     * Returns a truncate database operation.
     *
     * @param bool $cascadeTruncates Set to true to force truncates to cascade on databases that support this.
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    public static function TRUNCATE($cascadeTruncates = FALSE)
    {
        $truncate = new PHPUnit_Extensions_Database_Operation_Truncate();
        $truncate->setCascade($cascadeTruncates);

        return $truncate;
    }

    /**
     * Returns a delete database operation.
     *
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    public static function DELETE()
    {
        return new PHPUnit_Extensions_Database_Operation_Delete();
    }

    /**
     * Returns a delete_all database operation.
     *
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    public static function DELETE_ALL()
    {
        return new PHPUnit_Extensions_Database_Operation_DeleteAll();
    }

    /**
     * Returns an update database operation.
     *
     * @return PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    public static function UPDATE()
    {
        return new PHPUnit_Extensions_Database_Operation_Update();
    }

}
?>
