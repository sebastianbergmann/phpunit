<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 4.2.0
 */

/**
 *
 *
 * @package    PHPUnit
 * @author     Jeff Welch <whatthejeff@gmail.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 4.2.0
 */
class Util_RegexTest extends PHPUnit_Framework_TestCase
{
    public function validRegexpProvider()
    {
        return array(
          array('#valid regexp#', 'valid regexp', 1),
          array(';val.*xp;', 'valid regexp', 1),
          array('/val.*xp/i', 'VALID REGEXP', 1),
          array('/a val.*p/','valid regexp', 0),
        );
    }

    public function invalidRegexpProvider()
    {
        return array(
          array('valid regexp', 'valid regexp'),
          array(';val.*xp', 'valid regexp'),
          array('val.*xp/i', 'VALID REGEXP'),
        );
    }

    /**
     * @dataProvider validRegexpProvider
     * @covers       PHPUnit_Util_Regex::pregMatchSafe
     */
    public function testValidRegex($pattern, $subject, $return)
    {
        $this->assertEquals($return, PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
    }

    /**
     * @dataProvider invalidRegexpProvider
     * @covers       PHPUnit_Util_Regex::pregMatchSafe
     */
    public function testInvalidRegex($pattern, $subject)
    {
        $this->assertFalse(PHPUnit_Util_Regex::pregMatchSafe($pattern, $subject));
    }
}
