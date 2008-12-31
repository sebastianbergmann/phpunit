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
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Framework/MockObject/Builder/MethodNameMatch.php';
require_once 'PHPUnit/Framework/MockObject/Matcher.php';
require_once 'PHPUnit/Framework/MockObject/Stub.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Builder for mocked or stubbed invocations.
 *
 * Provides methods for building expectations without having to resort to
 * instantiating the various matchers manually. These methods also form a
 * more natural way of reading the expectation. This class should be together
 * with the test case PHPUnit_Framework_MockObject_TestCase.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Jan Borsodi <jb@ez.no>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Framework_MockObject_Builder_InvocationMocker implements PHPUnit_Framework_MockObject_Builder_MethodNameMatch
{
    protected $collection;

    protected $matcher;

    public function __construct(PHPUnit_Framework_MockObject_Stub_MatcherCollection $collection, PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher)
    {
        $this->collection = $collection;
        $this->matcher    = new PHPUnit_Framework_MockObject_Matcher($invocationMatcher);

        $this->collection->addMatcher($this->matcher);
    }

    public function getMatcher()
    {
        return $this->matcher;
    }

    public function id($id)
    {
        $this->collection->registerId($id, $this);

        return $this;
    }

    public function will(PHPUnit_Framework_MockObject_Stub $stub)
    {
        $this->matcher->stub = $stub;

        return $this;
    }

    public function after($id)
    {
        $this->matcher->afterMatchBuilderId = $id;

        return $this;
    }

    public function with()
    {
        $args = func_get_args();

        if ($this->matcher->methodNameMatcher === NULL) {
            throw new RuntimeException('Method name matcher is not defined, cannot define parameter matcher without one');
        }

        if ( $this->matcher->parametersMatcher !== NULL) {
            throw new RuntimeException('Parameter matcher is already defined, cannot redefine');
        }

        $this->matcher->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_Parameters($args);

        return $this;
    }

    public function withAnyParameters()
    {
        if ($this->matcher->methodNameMatcher === NULL) {
            throw new RuntimeException('Method name matcher is not defined, cannot define parameter matcher without one');
        }

        if ($this->matcher->parametersMatcher !== NULL) {
            throw new RuntimeException('Parameter matcher is already defined, cannot redefine');
        }

        $this->matcher->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_AnyParameters();

        return $this;
    }

    public function method($constraint)
    {
        if ($this->matcher->methodNameMatcher !== NULL) {
            throw new RuntimeException('Method name matcher is already defined, cannot redefine');
        }

        $this->matcher->methodNameMatcher = new PHPUnit_Framework_MockObject_Matcher_MethodName($constraint);

        return $this;
    }
}
?>
