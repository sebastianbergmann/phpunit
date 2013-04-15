<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

/**
 * Test helpers.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Test
{
    const REGEX_DATA_PROVIDER      = '/@dataProvider\s+([a-zA-Z0-9._:-\\\\x7f-\xff]+)/';
    const REGEX_EXPECTED_EXCEPTION = '(@expectedException\s+([:.\w\\\\x7f-\xff]+)(?:[\t ]+(\S*))?(?:[\t ]+(\S*))?\s*$)m';
    const REGEX_REQUIRES_VERSION   = '/@requires\s+(?P<name>PHP(?:Unit)?)\s+(?P<value>[\d\.-]+(dev|(RC|alpha|beta)[\d\.])?)[ \t]*\r?$/m';
    const REGEX_REQUIRES           = '/@requires\s+(?P<name>function|extension)\s(?P<value>([^ ]+))\r?$/m';

    const SMALL  = 0;
    const MEDIUM = 1;
    const LARGE  = 2;

    private static $annotationCache = array();

    protected static $templateMethods = array(
      'setUp', 'assertPreConditions', 'assertPostConditions', 'tearDown'
    );

    /**
     * @param  PHPUnit_Framework_Test $test
     * @param  boolean                $asString
     * @return mixed
     */
    public static function describe(PHPUnit_Framework_Test $test, $asString = TRUE)
    {
        if ($asString) {
            if ($test instanceof PHPUnit_Framework_SelfDescribing) {
                return $test->toString();
            } else {
                return get_class($test);
            }
        } else {
            if ($test instanceof PHPUnit_Framework_TestCase) {
                return array(
                  get_class($test), $test->getName()
                );
            }

            else if ($test instanceof PHPUnit_Framework_SelfDescribing) {
                return array('', $test->toString());
            }

            else {
                return array('', get_class($test));
            }
        }
    }

    /**
     * Returns the requirements for a test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 3.6.0
     */
    public static function getRequirements($className, $methodName)
    {
        $reflector  = new ReflectionClass($className);
        $docComment = $reflector->getDocComment();
        $reflector  = new ReflectionMethod($className, $methodName);
        $docComment .= "\n" . $reflector->getDocComment();
        $requires   = array();

        if ($count = preg_match_all(self::REGEX_REQUIRES_VERSION, $docComment, $matches)) {
            for ($i = 0; $i < $count; $i++) {
                $requires[$matches['name'][$i]] = $matches['value'][$i];
            }
        }
        if ($count = preg_match_all(self::REGEX_REQUIRES, $docComment, $matches)) {
            for ($i = 0; $i < $count; $i++) {
                $name = $matches['name'][$i] . 's';
                if (!isset($requires[$name])) {
                    $requires[$name] = array();
                }
                $requires[$name][] = $matches['value'][$i];
            }
        }

        return $requires;
    }

    /**
     * Returns the expected exception for a test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 3.3.6
     */
    public static function getExpectedException($className, $methodName)
    {
        $reflector  = new ReflectionMethod($className, $methodName);
        $docComment = $reflector->getDocComment();
        $docComment = substr($docComment, 3, -2);

        if (preg_match(self::REGEX_EXPECTED_EXCEPTION, $docComment, $matches)) {
            $annotations = self::parseTestMethodAnnotations(
              $className, $methodName
            );

            $class   = $matches[1];
            $code    = NULL;
            $message = '';

            if (isset($matches[2])) {
                $message = trim($matches[2]);
            }

            else if (isset($annotations['method']['expectedExceptionMessage'])) {
                $message = self::_parseAnnotationContent(
                    $annotations['method']['expectedExceptionMessage'][0]
                );
            }

            if (isset($matches[3])) {
                $code = $matches[3];
            }

            else if (isset($annotations['method']['expectedExceptionCode'])) {
                $code = self::_parseAnnotationContent(
                    $annotations['method']['expectedExceptionCode'][0]
                );
            }

            if (is_numeric($code)) {
                $code = (int)$code;
            }

            else if (is_string($code) && defined($code)) {
                $code = (int)constant($code);
            }

            return array(
              'class' => $class, 'code' => $code, 'message' => $message
            );
        }

        return FALSE;
    }

    /**
     * Parse annotation content to use constant/class constant values
     *
     * Constants are specified using a starting '@'. For example: @ClassName::CONST_NAME
     *
     * If the constant is not found the string is used as is to ensure maximum BC.
     *
     * @param  string $message
     * @return string
     */
    protected static function _parseAnnotationContent($message)
    {
        if (strpos($message, '::') !== FALSE && count(explode('::', $message) == 2)) {
            if (defined($message)) {
                $message = constant($message);
            }
        }
        return $message;
    }

    /**
     * Returns the provided data for a method.
     *
     * @param  string $className
     * @param  string $methodName
     * @param  string $docComment
     * @return mixed  array|Iterator when a data provider is specified and exists
     *                false          when a data provider is specified and does not exist
     *                null           when no data provider is specified
     * @since  Method available since Release 3.2.0
     */
    public static function getProvidedData($className, $methodName)
    {
        $reflector  = new ReflectionMethod($className, $methodName);
        $docComment = $reflector->getDocComment();
        $data       = NULL;

        if (preg_match(self::REGEX_DATA_PROVIDER, $docComment, $matches)) {
            $dataProviderMethodNameNamespace = explode('\\', $matches[1]);
            $leaf                            = explode('::', array_pop($dataProviderMethodNameNamespace));
            $dataProviderMethodName          = array_pop($leaf);

            if (!empty($dataProviderMethodNameNamespace)) {
                $dataProviderMethodNameNamespace = join('\\', $dataProviderMethodNameNamespace) . '\\';
            } else {
                $dataProviderMethodNameNamespace = '';
            }

            if (!empty($leaf)) {
                $dataProviderClassName = $dataProviderMethodNameNamespace . array_pop($leaf);
            } else {
                $dataProviderClassName = $className;
            }

            $dataProviderClass  = new ReflectionClass($dataProviderClassName);
            $dataProviderMethod = $dataProviderClass->getMethod(
              $dataProviderMethodName
            );

            if ($dataProviderMethod->isStatic()) {
                $object = NULL;
            } else {
                $object = $dataProviderClass->newInstance();
            }

            if ($dataProviderMethod->getNumberOfParameters() == 0) {
                $data = $dataProviderMethod->invoke($object);
            } else {
                $data = $dataProviderMethod->invoke($object, $methodName);
            }
        }

        if ($data !== NULL) {
            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    throw new PHPUnit_Framework_Exception(
                      sprintf(
                        'Data set %s is invalid.',
                        is_int($key) ? '#' . $key : '"' . $key . '"'
                      )
                    );
                }
            }
        }

        return $data;
    }

    /**
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @throws ReflectionException
     * @since  Method available since Release 3.4.0
     */
    public static function parseTestMethodAnnotations($className, $methodName = '')
    {
        if (!isset(self::$annotationCache[$className])) {
            $class = new ReflectionClass($className);
            self::$annotationCache[$className] = self::parseAnnotations($class->getDocComment());
        }

        if (!empty($methodName) && !isset(self::$annotationCache[$className . '::' . $methodName])) {
            try {
                $method = new ReflectionMethod($className, $methodName);
                $annotations = self::parseAnnotations($method->getDocComment());
            } catch (ReflectionException $e) {
                $annotations = array();
            }
            self::$annotationCache[$className . '::' . $methodName] = $annotations;
        }

        return array(
          'class'  => self::$annotationCache[$className],
          'method' => !empty($methodName) ? self::$annotationCache[$className . '::' . $methodName] : array()
        );
    }

    /**
     * @param  string $docblock
     * @return array
     * @since  Method available since Release 3.4.0
     */
    private static function parseAnnotations($docblock)
    {
        $annotations = array();
        // Strip away the docblock header and footer to ease parsing of one line annotations
        $docblock = substr($docblock, 3, -2);

        if (preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docblock, $matches)) {
            $numMatches = count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations[$matches['name'][$i]][] = $matches['value'][$i];
            }
        }

        return $annotations;
    }

    /**
     * Returns the backup settings for a test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public static function getBackupSettings($className, $methodName)
    {
        return array(
          'backupGlobals' => self::getBooleanAnnotationSetting(
            $className, $methodName, 'backupGlobals'
          ),
          'backupStaticAttributes' => self::getBooleanAnnotationSetting(
            $className, $methodName, 'backupStaticAttributes'
          )
        );
    }

    /**
     * Returns the dependencies for a test class or method.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public static function getDependencies($className, $methodName)
    {
        $annotations = self::parseTestMethodAnnotations(
          $className, $methodName
        );

        $dependencies = array();

        if (isset($annotations['class']['depends'])) {
            $dependencies = $annotations['class']['depends'];
        }

        if (isset($annotations['method']['depends'])) {
            $dependencies = array_merge(
              $dependencies, $annotations['method']['depends']
            );
        }

        return array_unique($dependencies);
    }

    /**
     * Returns the error handler settings for a test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    public static function getErrorHandlerSettings($className, $methodName)
    {
        return self::getBooleanAnnotationSetting(
          $className, $methodName, 'errorHandler'
        );
    }

    /**
     * Returns the groups for a test class or method.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 3.2.0
     */
    public static function getGroups($className, $methodName = '')
    {
        $annotations = self::parseTestMethodAnnotations(
          $className, $methodName
        );

        $groups = array();

        if (isset($annotations['method']['author'])) {
            $groups = $annotations['method']['author'];
        }

        else if (isset($annotations['class']['author'])) {
            $groups = $annotations['class']['author'];
        }

        if (isset($annotations['class']['group'])) {
            $groups = array_merge($groups, $annotations['class']['group']);
        }

        if (isset($annotations['method']['group'])) {
            $groups = array_merge($groups, $annotations['method']['group']);
        }

        if (isset($annotations['class']['ticket'])) {
            $groups = array_merge($groups, $annotations['class']['ticket']);
        }

        if (isset($annotations['method']['ticket'])) {
            $groups = array_merge($groups, $annotations['method']['ticket']);
        }

        foreach (array('small', 'medium', 'large') as $size) {
            if (isset($annotations['method'][$size])) {
                $groups[] = $size;
            }

            else if (isset($annotations['class'][$size])) {
                $groups[] = $size;
            }
        }

        return array_unique($groups);
    }

    /**
     * Returns the size of the test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return integer
     * @since  Method available since Release 3.6.0
     */
    public static function getSize($className, $methodName)
    {
        $groups = array_flip(self::getGroups($className, $methodName));
        $size   = self::SMALL;
        $class  = new ReflectionClass($className);

        if ((class_exists('PHPUnit_Extensions_Database_TestCase', FALSE) &&
             $class->isSubclassOf('PHPUnit_Extensions_Database_TestCase')) ||
            (class_exists('PHPUnit_Extensions_SeleniumTestCase', FALSE) &&
             $class->isSubclassOf('PHPUnit_Extensions_SeleniumTestCase'))) {
            $size = self::LARGE;
        }

        else if (isset($groups['medium'])) {
            $size = self::MEDIUM;
        }

        else if (isset($groups['large'])) {
            $size = self::LARGE;
        }

        return $size;
    }

    /**
     * Returns the tickets for a test class or method.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public static function getTickets($className, $methodName)
    {
        $annotations = self::parseTestMethodAnnotations(
          $className, $methodName
        );

        $tickets = array();

        if (isset($annotations['class']['ticket'])) {
            $tickets = $annotations['class']['ticket'];
        }

        if (isset($annotations['method']['ticket'])) {
            $tickets = array_merge($tickets, $annotations['method']['ticket']);
        }

        return array_unique($tickets);
    }

    /**
     * Returns the output buffering settings for a test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    public static function getOutputBufferingSettings($className, $methodName)
    {
        return self::getBooleanAnnotationSetting(
          $className, $methodName, 'outputBuffering'
        );
    }

    /**
     * Returns the process isolation settings for a test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return boolean
     * @since  Method available since Release 3.4.1
     */
    public static function getProcessIsolationSettings($className, $methodName)
    {
        $annotations = self::parseTestMethodAnnotations(
          $className, $methodName
        );

        if (isset($annotations['class']['runTestsInSeparateProcesses']) ||
            isset($annotations['method']['runInSeparateProcess'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Returns the preserve global state settings for a test.
     *
     * @param  string $className
     * @param  string $methodName
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    public static function getPreserveGlobalStateSettings($className, $methodName)
    {
        return self::getBooleanAnnotationSetting(
          $className, $methodName, 'preserveGlobalState'
        );
    }

    /**
     * @param  string $className
     * @param  string $methodName
     * @param  string $settingName
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    private static function getBooleanAnnotationSetting($className, $methodName, $settingName)
    {
        $annotations = self::parseTestMethodAnnotations(
          $className, $methodName
        );

        $result = NULL;

        if (isset($annotations['class'][$settingName])) {
            if ($annotations['class'][$settingName][0] == 'enabled') {
                $result = TRUE;
            }

            else if ($annotations['class'][$settingName][0] == 'disabled') {
                $result = FALSE;
            }
        }

        if (isset($annotations['method'][$settingName])) {
            if ($annotations['method'][$settingName][0] == 'enabled') {
                $result = TRUE;
            }

            else if ($annotations['method'][$settingName][0] == 'disabled') {
                $result = FALSE;
            }
        }

        return $result;
    }
}
