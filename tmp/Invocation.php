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

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Encapsulates information on a method invocation which can be passed to matchers.
 *
 * The invocation consists of the object it occured from, the class name, the
 * method name and all the parameters. The mock object must instantiate this
 * class with the values from the mocked method and pass it to an object of
 * PHPUnit_Framework_MockObject_Invokable.
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
class PHPUnit_Framework_MockObject_Invocation implements PHPUnit_Framework_SelfDescribing
{
    /**
     * @var object
     */
    public $object;

    /**
     * @var string
     */
    public $className;

    /**
     * @var string
     */
    public $methodName;

    /**
     * @var array
     */
    public $parameters;

    /**
     * @param string $className
     * @param string $methodname
     * @param array  $parameters
     * @param object $object
     */
    public function __construct($object, $className, $methodName, array $parameters)
    {
        $this->object     = $object;
        $this->className  = $className;
        $this->methodName = $methodName;
        $this->parameters = $parameters;

        foreach ($this->parameters as $key => $value) {
            if (is_object($value)) {
                $this->parameters[$key] = $this->cloneObject($value);
            }
        }
    }

    /**
     * @return string
     */
    public function toString()
    {
        return sprintf(
          "%s::%s(%s)",

          $this->className,
          $this->methodName,
          join(
            ', ',
            array_map(
              array('PHPUnit_Util_Type', 'shortenedExport'),
              $this->parameters
            )
          )
        );
    }

    /**
     * @param  object $original
     * @return object
     */
    protected function cloneObject($original)
    {
        $object = new ReflectionObject($original);

        if ($object->hasMethod('__clone')) {
            $method = $object->getMethod('__clone');

            if (!$method->isPublic()) {
                return $original;
            }

            try {
                return clone $original;
            }

            catch (Exception $e) {
                return $original;
            }
        }

        return clone $original;
    }
}
?>
