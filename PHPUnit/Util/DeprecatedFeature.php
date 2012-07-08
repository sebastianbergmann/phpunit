<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Framework
 * @author     Ralph Schindler <ralph.schindler@zend.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.7
 */

/**
 * Class to hold the information about a deprecated feature that was used
 *
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Ralph Schindler <ralph.schindler@zend.com>
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Interface available since Release 3.5.7
 */
class PHPUnit_Util_DeprecatedFeature
{
    /**
     * @var array
     */
    protected $traceInfo = array();

    /**
     * @var string
     */
    protected $message = NULL;

    /**
     * @param  string $message
     * @param  array  $traceInfo
     */
    public function __construct($message, array $traceInfo = array())
    {
        $this->message   = $message;
        $this->traceInfo = $traceInfo;
    }

    /**
     * Build a string representation of the deprecated feature that was raised
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';

        if (isset($this->traceInfo['file'])) {
            $string .= $this->traceInfo['file'];

            if (isset($this->traceInfo['line'])) {
                $string .= ':' . $this->traceInfo['line'] . ' - ';
            }
        }

        $string .= $this->message;

        return $string;
    }
}
