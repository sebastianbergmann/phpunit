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
 * @subpackage Extensions_Database_Operation
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

/**
 * Thrown for exceptions encountered with database operations. Provides
 * information regarding which operations failed and the query (if any) it
 * failed on.
 *
 * @package    PHPUnit
 * @subpackage Extensions_Database_Operation
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Extensions_Database_Operation_Exception extends RuntimeException
{

    /**
     * @var string
     */
    protected $operation;

    /**
     * @var string
     */
    protected $preparedQuery;

    /**
     * @var array
     */
    protected $preparedArgs;

    /**
     * @var PHPUnit_Extensions_Database_DataSet_ITable
     */
    protected $table;

    /**
     * @var string
     */
    protected $error;

    /**
     * Creates a new dbunit operation exception
     *
     * @param string $operation
     * @param string $current_query
     * @param PHPUnit_Extensions_Database_DataSet_ITable $current_table
     * @param string $error
     */
    public function __construct($operation, $current_query, $current_args, $current_table, $error)
    {
        parent::__construct("{$operation} operation failed on query: {$current_query} using args: " . print_r($current_args, TRUE) . " [{$error}]");
        $this->operation = $operation;
        $this->preparedQuery = $current_query;
        $this->preparedArgs = $current_args;
        $this->table = $current_table;
        $this->error = $error;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function getQuery()
    {
        return $this->preparedQuery;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getArgs()
    {
        return $this->preparedArgs;
    }

    public function getError()
    {
        return $this->error;
    }
}
