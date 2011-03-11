<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Util
 * @author     Florian Zumkeller-Quast <branleb@googlemail.com>
 * @copyright  2002-2011 Florian Zumkeller-Quast <branleb@googlemail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      2011-03-11
 */

/**
 * Utility safe usage of the PHP unserialize method.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Florian Zumkeller-Quast <branleb@googlemail.com>
 * @copyright  2002-2011 Florian Zumkeller-Quast <branleb@googlemail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      2011-03-11
 */
class PHPUnit_Util_Unserialize
{
    /**
     * Create a factory instance
     *
     * @return PHPUnit_Util_Unserialize
     */
    public function factory()
    {
      return new static;
    }

    /**
     * Unserializes a PHP serialized string safe without usage of the @ operator
     *
     * @param  string $serialized
     *
     * @return mixed the unserialized data
     *
     * @throws ErrorException
     */
    public function unserialize(serialized)
    {
        try {
            //! Setup the custom error handler to catch error triggered by unserialize
            set_error_handler(array($this, 'handleError'));
            $unserialized = unserialize($serialized);
            restore_error_handler();
            return $unserialized;
        } catch(ErrorException $excp) {
            //! Restore the old error handler and rethrow the exeption
            restore_error_handler();
            throw $excp;
        }
    }

    /**
     * Custom error handler just used for the handling of php error  triggered by unserialize()
     *   *
     * @param integer $errno   The error number
     * @param string  $errstr  The error message
     * @param string  $errfile The file where the error occured
     * @param integer $errline The line where the error occured
     *
     * @throws ErrorException for the PHP error
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }
}
