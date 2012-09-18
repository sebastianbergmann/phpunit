<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2010-2012, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2010-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      File available since Release 1.0.0
 */

/**
 * Represents a static invocation.
 *
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2010-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version    Release: @package_version@
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      Class available since Release 1.0.0
 */
class PHPUnit_Framework_MockObject_Invocation_Static implements PHPUnit_Framework_MockObject_Invocation, PHPUnit_Framework_SelfDescribing
{
    /**
     * @var array
     */
    protected static $uncloneableExtensions = array(
      'mysqli' => TRUE,
      'SQLite' => TRUE,
      'sqlite3' => TRUE,
      'tidy' => TRUE,
      'xmlwriter' => TRUE,
      'xsl' => TRUE
    );

    /**
     * @var array
     */
    protected static $uncloneableClasses = array(
      'Closure',
      'COMPersistHelper',
      'IteratorIterator',
      'RecursiveIteratorIterator',
      'SplFileObject',
      'PDORow',
      'ZipArchive'
    );

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
     * @param string  $className
     * @param string  $methodname
     * @param array   $parameters
     * @param boolean $cloneObjects
     */
    public function __construct($className, $methodName, array $parameters, $cloneObjects = FALSE)
    {
        $this->className  = $className;
        $this->methodName = $methodName;
        $this->parameters = $parameters;

        if (!$cloneObjects) {
            return;
        }

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
        $cloneable = NULL;
        $object    = new ReflectionObject($original);

        // Check the blacklist before asking PHP reflection to work around
        // https://bugs.php.net/bug.php?id=53967
        if ($object->isInternal() &&
            isset(self::$uncloneableExtensions[$object->getExtensionName()])) {
            $cloneable = FALSE;
        }

        if ($cloneable === NULL) {
            foreach (self::$uncloneableClasses as $class) {
                if ($original instanceof $class) {
                    $cloneable = FALSE;
                    break;
                }
            }
        }

        if ($cloneable === NULL && method_exists($object, 'isCloneable')) {
            $cloneable = $object->isCloneable();
        }

        if ($cloneable === NULL && $object->hasMethod('__clone')) {
            $method    = $object->getMethod('__clone');
            $cloneable = $method->isPublic();
        }

        if ($cloneable === NULL) {
            $cloneable = TRUE;
        }

        if ($cloneable) {
            try {
                return clone $original;
            }

            catch (Exception $e) {
                return $original;
            }
        } else {
            return $original;
        }
    }
}
