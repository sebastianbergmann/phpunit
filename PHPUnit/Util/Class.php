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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Class helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 */
class PHPUnit_Util_Class
{
    protected static $buffer = array();
    protected static $fileClassMap = array();
    protected static $fileFunctionMap = array();

    /**
     * Starts the collection of loaded classes.
     *
     */
    public static function collectStart()
    {
        self::$buffer = get_declared_classes();
    }

    /**
     * Stops the collection of loaded classes and
     * returns the names of the loaded classes.
     *
     * @return array
     */
    public static function collectEnd()
    {
        return array_values(
          array_diff(get_declared_classes(), self::$buffer)
        );
    }

    /**
     * Stops the collection of loaded classes and
     * returns the names of the files that declare the loaded classes.
     *
     * @return array
     */
    public static function collectEndAsFiles()
    {
        $result = self::collectEnd();
        $count  = count($result);

        for ($i = 0; $i < $count; $i++) {
            $class = new ReflectionClass($result[$i]);

            if ($class->isUserDefined()) {
                $file = $class->getFileName();

                if (file_exists($file)) {
                    $result[$i] = $file;
                } else {
                    unset($result[$i]);
                }
            }
        }

        return $result;
    }

    /**
     * Returns the names of the classes declared in a sourcefile.
     *
     * @param  string  $filename
     * @param  string  $commonPath
     * @param  boolean $clearCache
     * @return array
     */
    public static function getClassesInFile($filename, $commonPath = '', $clearCache = FALSE)
    {
        if ($commonPath != '') {
            $filename = str_replace($commonPath, '', $filename);
        }

        if ($clearCache) {
            self::$fileClassMap = array();
        }

        if (empty(self::$fileClassMap)) {
            $classes = array_merge(get_declared_classes(), get_declared_interfaces());

            foreach ($classes as $className) {
                $class = new ReflectionClass($className);

                if ($class->isUserDefined()) {
                    $file = $class->getFileName();

                    if ($commonPath != '') {
                        $file = str_replace($commonPath, '', $file);
                    }

                    if (!isset(self::$fileClassMap[$file])) {
                        self::$fileClassMap[$file] = array($class);
                    } else {
                        self::$fileClassMap[$file][] = $class;
                    }
                }
            }
        }

        return isset(self::$fileClassMap[$filename]) ? self::$fileClassMap[$filename] : array();
    }

    /**
     * Returns the names of the classes declared in a sourcefile.
     *
     * @param  string  $filename
     * @param  string  $commonPath
     * @param  boolean $clearCache
     * @return array
     * @since  Method available since Release 3.2.0
     * @todo   Find a better place for this method.
     */
    public static function getFunctionsInFile($filename, $commonPath = '', $clearCache = FALSE)
    {
        if ($commonPath != '') {
            $filename = str_replace($commonPath, '', $filename);
        }

        if ($clearCache) {
            self::$fileFunctionMap = array();
        }

        if (empty(self::$fileFunctionMap)) {
            $functions = get_defined_functions();

            foreach ($functions['user'] as $functionName) {
                $function = new ReflectionFunction($functionName);

                $file = $function->getFileName();

                if ($commonPath != '') {
                    $file = str_replace($commonPath, '', $file);
                }

                if (!isset(self::$fileFunctionMap[$file])) {
                    self::$fileFunctionMap[$file] = array($function);
                } else {
                    self::$fileFunctionMap[$file][] = $function;
                }
            }
        }

        return isset(self::$fileFunctionMap[$filename]) ? self::$fileFunctionMap[$filename] : array();
    }

    /**
     * Returns the class hierarchy for a given class.
     *
     * @param  string  $className
     * @param  boolean $asReflectionObjects
     * @return array
     */
    public static function getHierarchy($className, $asReflectionObjects = FALSE)
    {
        if ($asReflectionObjects) {
            $classes = array(new ReflectionClass($className));
        } else {
            $classes = array($className);
        }

        $done    = FALSE;

        while (!$done) {
            if ($asReflectionObjects) {
                $class = new ReflectionClass($classes[count($classes)-1]->getName());
            } else {
                $class = new ReflectionClass($classes[count($classes)-1]);
            }

            $parent = $class->getParentClass();

            if ($parent !== FALSE) {
                if ($asReflectionObjects) {
                    $classes[] = $parent;
                } else {
                    $classes[] = $parent->getName();
                }
            } else {
                $done = TRUE;
            }
        }

        return $classes;
    }

    /**
     * Returns the signature of a function.
     *
     * @param  ReflectionFunction $function
     * @return string
     * @since  Method available since Release 3.3.2
     * @todo   Find a better place for this method.
     */
    public static function getFunctionSignature(ReflectionFunction $function)
    {
        if ($function->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }

        return sprintf(
          'function %s%s(%s)',

          $reference,
          $function->getName(),
          self::getMethodParameters($function)
        );
    }

    /**
     * Returns the signature of a method.
     *
     * @param  ReflectionMethod $method
     * @return string
     * @since  Method available since Release 3.2.0
     */
    public static function getMethodSignature(ReflectionMethod $method)
    {
        if ($method->isPrivate()) {
            $modifier = 'private';
        }

        else if ($method->isProtected()) {
            $modifier = 'protected';
        }

        else {
            $modifier = 'public';
        }

        if ($method->isStatic()) {
            $modifier .= ' static';
        }

        if ($method->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }

        return sprintf(
          '%s function %s%s(%s)',

          $modifier,
          $reference,
          $method->getName(),
          self::getMethodParameters($method)
        );
    }

    /**
     * Returns the parameters of a function or method.
     *
     * @param  ReflectionFunction|ReflectionMethod $method
     * @return string
     * @since  Method available since Release 3.2.0
     */
    public static function getMethodParameters($method)
    {
        $parameters = array();

        foreach ($method->getParameters() as $i => $parameter) {
            $name = '$' . $parameter->getName();

            if ($name === '$') {
                $name .= 'arg' . $i;
            }

            $typeHint = '';

            if ($parameter->isArray()) {
                $typeHint = 'array ';
            } else {
                try {
                    $class = $parameter->getClass();
                }

                catch (ReflectionException $e) {
                    $class = FALSE;
                }

                if ($class) {
                    $typeHint = $class->getName() . ' ';
                }
            }

            $default = '';

            if ($parameter->isDefaultValueAvailable()) {
                $value   = $parameter->getDefaultValue();
                $default = ' = ' . var_export($value, TRUE);
            }

            else if ($parameter->isOptional()) {
                $default = ' = null';
            }

            $ref = '';

            if ($parameter->isPassedByReference()) {
                $ref = '&';
            }

            $parameters[] = $typeHint . $ref . $name . $default;
        }

        return join(', ', $parameters);
    }

    /**
     * Returns the sourcecode of a user-defined class.
     *
     * @param  string  $className
     * @param  string  $methodName
     * @return mixed
     */
    public static function getMethodSource($className, $methodName)
    {
        if ($className != 'global') {
            $function = new ReflectionMethod($className, $methodName);
        } else {
            $function = new ReflectionFunction($methodName);
        }

        $filename = $function->getFileName();

        if (file_exists($filename)) {
            $file   = file($filename);
            $result = '';
            $start  = $function->getStartLine() - 1;
            $end    = $function->getEndLine() - 1;

            for ($line = $start; $line <= $end; $line++) {
                $result .= $file[$line];
            }

            return $result;
        } else {
            return FALSE;
        }
    }

    /**
     * Returns the package information of a user-defined class.
     *
     * @param  string $className
     * @return array
     */
    public static function getPackageInformation($className)
    {
        $result = array(
          'namespace'   => '',
          'fullPackage' => '',
          'category'    => '',
          'package'     => '',
          'subpackage'  => ''
        );

        if (strpos($className, ':') !== FALSE) {
            $result['namespace'] = self::arrayToName(
              explode('\\', $className), '\\'
            );
        }

        $class      = new ReflectionClass($className);
        $docComment = $class->getDocComment();

        if (preg_match('/@category[\s]+([\.\w]+)/', $docComment, $matches)) {
            $result['category'] = $matches[1];
        }

        if (preg_match('/@package[\s]+([\.\w]+)/', $docComment, $matches)) {
            $result['package']     = $matches[1];
            $result['fullPackage'] = $matches[1];
        }

        if (preg_match('/@subpackage[\s]+([\.\w]+)/', $docComment, $matches)) {
            $result['subpackage'] = $matches[1];
            $result['fullPackage'] .= '.' . $matches[1];
        }

        if (empty($result['fullPackage'])) {
            $result['fullPackage'] = self::arrayToName(
              explode('_', str_replace('\\', '_', $className)), '.'
            );
        }

        return $result;
    }

    /**
     * Returns the package information of a user-defined class.
     *
     * @param  array $parts
     * @param  string $join
     * @return string
     * @since  Method available since Release 3.2.12
     */
    protected static function arrayToName(array $parts, $join)
    {
        $result = '';

        if (count($parts) > 1) {
            array_pop($parts);

            $result = join($join, $parts);
        }

        return $result;
    }
}
?>
