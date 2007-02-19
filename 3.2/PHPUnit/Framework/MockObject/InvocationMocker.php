<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Framework/MockObject/Builder/InvocationMocker.php';
require_once 'PHPUnit/Framework/MockObject/Builder/Match.php';
require_once 'PHPUnit/Framework/MockObject/Builder/Namespace.php';
require_once 'PHPUnit/Framework/MockObject/Matcher.php';
require_once 'PHPUnit/Framework/MockObject/Stub.php';
require_once 'PHPUnit/Framework/MockObject/Invocation.php';
require_once 'PHPUnit/Framework/MockObject/Invokable.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Mocker for invocations which are sent from
 * PHPUnit_Framework_MockObject_MockObject objects.
 *
 * Keeps track of all expectations and stubs as well as registering
 * identifications for builders.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_MockObject_InvocationMocker implements PHPUnit_Framework_MockObject_Stub_MatcherCollection, PHPUnit_Framework_MockObject_Invokable, PHPUnit_Framework_MockObject_Builder_Namespace
{
    private $matchers = array();

    private $builderMap = array();

    public function addMatcher(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        $this->matchers[] = $matcher;
    }

    public function lookupId($id)
    {
        if (isset($this->builderMap[$id])) {
            return $this->builderMap[$id];
        }

        return NULL;
    }

    public function registerId($id, PHPUnit_Framework_MockObject_Builder_Match $builder)
    {
        if (isset($this->builderMap[$id])) {
            throw new RuntimeException("Match builder with id <{$id}> is already registered.");
        }

        $this->builderMap[$id] = $builder;
    }

    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher)
    {
        $builder = new PHPUnit_Framework_MockObject_Builder_InvocationMocker($this, $matcher);

        return $builder;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $hasReturnValue = FALSE;

        if (strtolower($invocation->methodName) == '__tostring') {
            $returnValue = '';
        } else {
            $returnValue = NULL;
        }

        foreach($this->matchers as $match) {
            if ($match->matches($invocation)) {
                $value = $match->invoked($invocation);

                if (!$hasReturnValue) {
                    $returnValue    = $value;
                    $hasReturnValue = TRUE;
                }
            }
        }

        return $returnValue;
    }

    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        foreach($this->matchers as $matcher) {
            if (!$matcher->matches($invocation)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function verify()
    {
        foreach($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
}
?>
