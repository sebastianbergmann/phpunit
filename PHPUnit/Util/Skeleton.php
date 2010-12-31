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
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.1.0
 */

/**
 * Generator for skeletons.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
abstract class PHPUnit_Util_Skeleton
{
    /**
     * @var array
     */
    protected $inClassName;

    /**
     * @var string
     */
    protected $inSourceFile;

    /**
     * @var array
     */
    protected $outClassName;

    /**
     * @var string
     */
    protected $outSourceFile;

    /**
     * Constructor.
     *
     * @param string $inClassName
     * @param string $inSourceFile
     * @param string $outClassName
     * @param string $outSourceFile
     * @since Method available since Release 3.4.0
     */
    public function __construct($inClassName, $inSourceFile = '', $outClassName = '', $outSourceFile = '')
    {
        $this->inClassName = PHPUnit_Util_Class::parseFullyQualifiedClassName(
          $inClassName
        );

        $this->outClassName = PHPUnit_Util_Class::parseFullyQualifiedClassName(
          $outClassName
        );

        $this->inSourceFile = str_replace(
          $this->inClassName['fullyQualifiedClassName'],
          $this->inClassName['className'],
          $inSourceFile
        );

        $this->outSourceFile = str_replace(
          $this->outClassName['fullyQualifiedClassName'],
          $this->outClassName['className'],
          $outSourceFile
        );
    }

    /**
     * @return string
     */
    public function getOutClassName()
    {
        return $this->outClassName['fullyQualifiedClassName'];
    }

    /**
     * @return string
     */
    public function getOutSourceFile()
    {
        return $this->outSourceFile;
    }

    /**
     * Generates the code and writes it to a source file.
     *
     * @param  string  $file
     */
    public function write($file = '')
    {
        if ($file == '') {
            $file = $this->outSourceFile;
        }

        if ($fp = @fopen($file, 'wt')) {
            @fwrite($fp, $this->generate());
            @fclose($fp);
        }
    }

    abstract public function generate();
}
