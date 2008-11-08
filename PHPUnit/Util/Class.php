<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.0
 */

if (!defined('T_NAMESPACE')) {
    define('T_NAMESPACE', 377);
}

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Class helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 */
class PHPUnit_Util_Class
{
    protected static $buffer = array();
    protected static $fileMap = array();

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
     * Returns information on the classes declared in a sourcefile.
     *
     * @param  string $filename
     * @return array
     */
    public static function getClassesInFile($filename)
    {
        if (!isset(self::$fileMap[$filename])) {
            self::parseFile($filename);
        }

        return self::$fileMap[$filename]['classes'];
    }

    /**
     * Returns information on the functions declared in a sourcefile.
     *
     * @param  string $filename
     * @return array
     * @since  Method available since Release 3.2.0
     * @todo   Find a better place for this method.
     */
    public static function getFunctionsInFile($filename)
    {
        if (!isset(self::$fileMap[$filename])) {
            self::parseFile($filename);
        }

        return self::$fileMap[$filename]['functions'];
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
     * Returns the parameters of a function or method.
     *
     * @param  ReflectionFunction|ReflectionMethod $method
     * @return string
     * @since  Method available since Release 3.2.0
     */
    public static function getMethodParameters($method)
    {
        $parameters = array();

        foreach ($method->getParameters() as $parameter) {
            $name     = '$' . $parameter->getName();
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
     * @param  string $docComment
     * @return array
     */
    public static function getPackageInformation($className, $docComment)
    {
        $result = array(
          'namespace'   => '',
          'fullPackage' => '',
          'category'    => '',
          'package'     => '',
          'subpackage'  => ''
        );

        if (strpos($className, '\\') !== FALSE) {
            $result['namespace'] = self::arrayToName(
              explode('\\', $className), '\\'
            );
        }

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
     * Returns the value of a static attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param  string  $className
     * @param  string  $attributeName
     * @return mixed
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.4.0
     */
    public static function getStaticAttribute($className, $attributeName)
    {
        if (!is_string($className) || !class_exists($className) || !is_string($attributeName)) {
            throw new InvalidArgumentException;
        }

        $class      = new ReflectionClass($className);
        $attributes = $class->getStaticProperties();

        if (isset($attributes[$attributeName])) {
            return $attributes[$attributeName];
        }

        if (version_compare(PHP_VERSION, '5.2', '<')) {
            $protectedName = "\0*\0" . $attributeName;
        } else {
            $protectedName = '*' . $attributeName;
        }

        if (isset($attributes[$protectedName])) {
            return $attributes[$protectedName];
        }

        $classes = self::getHierarchy($className);

        foreach ($classes as $class) {
            $privateName = sprintf(
              "\0%s\0%s",

              $class,
              $attributeName
            );

            if (isset($attributes[$privateName])) {
                return $attributes[$privateName];
            }
        }

        throw new RuntimeException(
          sprintf(
            'Attribute "%s" not found in class.',

            $attributeName
          )
        );
    }

    /**
     * Returns the value of an object's attribute.
     * This also works for attributes that are declared protected or private.
     *
     * @param  object  $object
     * @param  string  $attributeName
     * @return mixed
     * @throws InvalidArgumentException
     * @since  Method available since Release 3.4.0
     */
    public static function getObjectAttribute($object, $attributeName)
    {
        if (!is_object($object) || !is_string($attributeName)) {
            throw new InvalidArgumentException;
        }

        PHPUnit_Framework_Assert::assertObjectHasAttribute($attributeName, $object);
        $attribute = new ReflectionProperty($object, $attributeName);

        if ($attribute->isPublic()) {
            return $object->$attributeName;
        } else {
            $array         = (array)$object;
            $protectedName = "\0*\0" . $attributeName;

            if (array_key_exists($protectedName, $array)) {
                return $array[$protectedName];
            } else {
                $classes = self::getHierarchy(get_class($object));

                foreach ($classes as $class) {
                    $privateName = sprintf(
                      "\0%s\0%s",

                      $class,
                      $attributeName
                    );

                    if (array_key_exists($privateName, $array)) {
                        return $array[$privateName];
                    }
                }
            }
        }

        throw new RuntimeException(
          sprintf(
            'Attribute "%s" not found in object.',

            $attributeName
          )
        );
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

    /**
     * Parses a file for class, method, and function information.
     *
     * @param string $filename
     * @since Method available since Release 3.4.0
     */
    protected static function parseFile($filename)
    {
        self::$fileMap[$filename] = array(
          'classes' => array(), 'functions' => array()
        );

        $tokens                     = token_get_all(file_get_contents($filename));
        $numTokens                  = count($tokens);
        $blocks                     = array();
        $line                       = 1;
        $name                       = array();
        $currentBlock               = FALSE;
        $currentNamespace           = FALSE;
        $currentClass               = FALSE;
        $currentFunction            = FALSE;
        $currentFunctionStartLine   = FALSE;
        $currentDocComment          = FALSE;
        $currentSignature           = FALSE;
        $currentSignatureStartToken = FALSE;

        for ($i = 0; $i < $numTokens; $i++) {
            if (is_string($tokens[$i])) {
                if ($tokens[$i] == '{') {
                    if ($currentBlock == T_CLASS) {
                        $block = $currentClass;
                    }

                    else if ($currentBlock == T_FUNCTION) {
                        $currentSignature = '';

                        for ($j = $currentSignatureStartToken; $j < $i; $j++) {
                            if (is_string($tokens[$j])) {
                                $currentSignature .= $tokens[$j];
                            } else {
                                $currentSignature .= $tokens[$j][1];
                            }
                        }

                        $currentSignature = trim($currentSignature);

                        $block                      = $currentFunction;
                        $currentSignatureStartToken = FALSE;
                    }

                    else {
                        $block = FALSE;
                    }

                    array_push($blocks, $block);

                    $currentBlock = FALSE;
                }

                else if ($tokens[$i] == '}') {
                    $block = array_pop($blocks);

                    if ($block !== FALSE && $block !== NULL) {
                        if ($block == $currentClass) {
                            self::$fileMap[$filename]['classes'][$currentClass]['endLine'] = $line;

                            $currentClass          = FALSE;
                            $currentClassStartLine = FALSE;
                        }

                        else if ($block == $currentFunction) {
                            if ($currentDocComment !== FALSE) {
                                $docComment        = $currentDocComment;
                                $currentDocComment = FALSE;
                            } else {
                                $docComment = '';
                            }

                            $tmp = array(
                              'docComment' => $docComment,
                              'signature'  => $currentSignature,
                              'startLine'  => $currentFunctionStartLine,
                              'endLine'    => $line
                            );

                            if ($currentClass === FALSE) {
                                self::$fileMap[$filename]['functions'][$currentFunction] = $tmp;
                            } else {
                                self::$fileMap[$filename]['classes'][$currentClass]['methods'][$currentFunction] = $tmp;
                            }

                            $currentFunction          = FALSE;
                            $currentFunctionStartLine = FALSE;
                            $currentSignature         = FALSE;
                        }
                    }
                }

                continue;
            }

            switch ($tokens[$i][0]) {
                case T_NAMESPACE: {
                    $currentNamespace = $tokens[$i+2][1];
                }
                break;

                case T_CLASS: {
                    $currentBlock = T_CLASS;

                    if ($currentNamespace === FALSE) {
                        $currentClass = $tokens[$i+2][1];
                    } else {
                        $currentClass = $currentNamespace . '\\' . $tokens[$i+2][1];
                    }

                    if ($currentDocComment !== FALSE) {
                        $docComment        = $currentDocComment;
                        $currentDocComment = FALSE;
                    } else {
                        $docComment = '';
                    }

                    self::$fileMap[$filename]['classes'][$currentClass] = array(
                      'methods'    => array(),
                      'docComment' => $docComment,
                      'startLine'  => $line
                    );
                }
                break;

                case T_FUNCTION: {
                    $currentBlock             = T_FUNCTION;
                    $currentFunctionStartLine = $line;

                    $done                       = FALSE;
                    $currentSignatureStartToken = $i - 1;

                    do {
                        switch ($tokens[$currentSignatureStartToken][0]) {
                            case T_ABSTRACT:
                            case T_FINAL:
                            case T_PRIVATE:
                            case T_PUBLIC:
                            case T_PROTECTED:
                            case T_STATIC:
                            case T_WHITESPACE: {
                                $currentSignatureStartToken--;
                            }
                            break;

                            default: {
                                $currentSignatureStartToken++;
                                $done = TRUE;
                            }
                        }
                    }
                    while (!$done);

                    if (isset($tokens[$i+2][1])) {
                        $functionName = $tokens[$i+2][1];
                    }

                    else if (isset($tokens[$i+3][1])) {
                        $functionName = $tokens[$i+3][1];
                    }

                    if ($currentNamespace === FALSE) {
                        $currentFunction = $functionName;
                    } else {
                        $currentFunction = $currentNamespace . '\\' . $functionName;
                    }
                }
                break;

                case T_DOC_COMMENT: {
                    $currentDocComment = $tokens[$i][1];
                }
                break;
            }

            $line += substr_count($tokens[$i][1], "\n");
        }
    }
}
?>
