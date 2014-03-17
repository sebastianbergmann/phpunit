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
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

if (!function_exists('trait_exists')) {
    function trait_exists($traitname, $autoload = true)
    {
        return false;
    }
}

/**
 * Test helpers.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Test
{
    const REGEX_DATA_PROVIDER      = '/@dataProvider\s+([a-zA-Z0-9._:-\\\\x7f-\xff]+)/';
    const REGEX_EXPECTED_EXCEPTION = '(@expectedException\s+([:.\w\\\\x7f-\xff]+)(?:[\t ]+(\S*))?(?:[\t ]+(\S*))?\s*$)m';
    const REGEX_REQUIRES_VERSION   = '/@requires\s+(?P<name>PHP(?:Unit)?)\s+(?P<value>[\d\.-]+(dev|(RC|alpha|beta)[\d\.])?)[ \t]*\r?$/m';
    const REGEX_REQUIRES_OS        = '/@requires\s+OS\s+(?P<value>.+?)[ \t]*\r?$/m';
    const REGEX_REQUIRES           = '/@requires\s+(?P<name>function|extension)\s+(?P<value>([^ ]+?))[ \t]*\r?$/m';

    const SMALL  = 0;
    const MEDIUM = 1;
    const LARGE  = 2;

    private static $annotationCache = array();

    private static $templateMethods = array(
      'setUp', 'assertPreConditions', 'assertPostConditions', 'tearDown'
    );

    private static $hookMethods = array();

    /**
     * @param  PHPUnit_Framework_Test $test
     * @param  boolean                $asString
     * @return mixed
     */
    public static function describe(PHPUnit_Framework_Test $test, $asString = true)
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
            } elseif ($test instanceof PHPUnit_Framework_SelfDescribing) {
                return array('', $test->toString());
            } else {
                return array('', get_class($test));
            }
        }
    }

    /**
     * @param  string     $className
     * @param  string     $methodName
     * @return array|bool
     * @throws PHPUnit_Framework_CodeCoverageException
     * @since  Method available since Release 4.0.0
     */
    public static function getLinesToBeCovered($className, $methodName)
    {
        $codeToCoverList = array();

        $class = new ReflectionClass($className);

        try {
            $method = new ReflectionMethod($className, $methodName);
        } catch (ReflectionException $e) {
            return array();
        }

        $docComment = self::getDocCommentsOfTestClassAndTestMethodAndTemplateMethods($class, $method);

        if (strpos($docComment, '@coversNothing') !== false) {
            return false;
        }

        $classShortcut = preg_match_all(
          '(@coversDefaultClass\s+(?P<coveredClass>[^\s]++)\s*$)m',
          $class->getDocComment(),
          $matches
        );

        if ($classShortcut) {
            if ($classShortcut > 1) {
                throw new PHPUnit_Framework_CodeCoverageException(
                  sprintf(
                    'More than one @coversClass annotation in class or interface "%s".',
                    $className
                  )
                );
            }

            $classShortcut = $matches['coveredClass'][0];
        }

        $match = preg_match_all(
          '(@covers\s+(?P<coveredElement>[^\s()]++)[\s()]*$)m',
          $docComment,
          $matches
        );

        if ($match) {
            foreach ($matches['coveredElement'] as $coveredElement) {
                if ($classShortcut && strncmp($coveredElement, '::', 2) === 0) {
                    $coveredElement = $classShortcut . $coveredElement;
                }

                $codeToCoverList = array_merge(
                  $codeToCoverList,
                  self::resolveElementToReflectionObjects($coveredElement)
                );
            }
        }

        return self::resolveReflectionObjectsToLines($codeToCoverList);
    }

    /**
     * Returns lines of code specified with the @uses annotation.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 4.0.0
     */
    public static function getLinesToBeUsed($className, $methodName)
    {
        $annotations = self::parseTestMethodAnnotations(
          $className, $methodName
        );

        $uses = array();

        if (isset($annotations['class']['uses'])) {
            $uses = $annotations['class']['uses'];
        }

        if (isset($annotations['method']['uses'])) {
            $uses = array_merge($uses, $annotations['method']['uses']);
        }

        $uses          = array_unique($uses);
        $codeToUseList = array();

        foreach (array_unique($uses) as $element) {
            $codeToUseList = array_merge(
              $codeToUseList,
              self::resolveElementToReflectionObjects($element)
            );
        }

        return self::resolveReflectionObjectsToLines($codeToUseList);
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

        if ($count = preg_match_all(self::REGEX_REQUIRES_OS, $docComment, $matches)) {
            $requires['OS'] = sprintf(
              '/%s/i',
              addcslashes($matches['value'][$count - 1], '/')
            );
        }
        if ($count = preg_match_all(self::REGEX_REQUIRES_VERSION, $docComment, $matches)) {
            for ($i = 0; $i < $count; $i++) {
                $requires[$matches['name'][$i]] = $matches['value'][$i];
            }
        }

        // https://bugs.php.net/bug.php?id=63055
        $matches = array();

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
            $code    = null;
            $message = '';

            if (isset($matches[2])) {
                $message = trim($matches[2]);
            } elseif (isset($annotations['method']['expectedExceptionMessage'])) {
                $message = self::parseAnnotationContent(
                    $annotations['method']['expectedExceptionMessage'][0]
                );
            }

            if (isset($matches[3])) {
                $code = $matches[3];
            } elseif (isset($annotations['method']['expectedExceptionCode'])) {
                $code = self::parseAnnotationContent(
                    $annotations['method']['expectedExceptionCode'][0]
                );
            }

            if (is_numeric($code)) {
                $code = (int) $code;
            } elseif (is_string($code) && defined($code)) {
                $code = (int) constant($code);
            }

            return array(
              'class' => $class, 'code' => $code, 'message' => $message
            );
        }

        return false;
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
    private static function parseAnnotationContent($message)
    {
        if (strpos($message, '::') !== false && count(explode('::', $message) == 2)) {
            if (defined($message)) {
                $message = constant($message);
            }
        }

        return $message;
    }

    /**
     * Returns the provided data for a method.
     *
     * @param  string           $className
     * @param  string           $methodName
     * @return array|Iterator when a data provider is specified and exists
     *         false          when a data provider is specified but does not exist
     *         null           when no data provider is specified
     * @throws PHPUnit_Framework_Exception
     * @since  Method available since Release 3.2.0
     */
    public static function getProvidedData($className, $methodName)
    {
        $reflector  = new ReflectionMethod($className, $methodName);
        $docComment = $reflector->getDocComment();
        $data       = null;

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
                $object = null;
            } else {
                $object = $dataProviderClass->newInstance();
            }

            if ($dataProviderMethod->getNumberOfParameters() == 0) {
                $data = $dataProviderMethod->invoke($object);
            } else {
                $data = $dataProviderMethod->invoke($object, $methodName);
            }
        }

        if ($data !== null) {
            if (is_object($data)) {
                $data = iterator_to_array($data);
            }

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
     * @param  string              $className
     * @param  string              $methodName
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
     * @param  string  $className
     * @param  string  $methodName
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
        } elseif (isset($annotations['class']['author'])) {
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
            } elseif (isset($annotations['class'][$size])) {
                $groups[] = $size;
            }
        }

        return array_unique($groups);
    }

    /**
     * Returns the size of the test.
     *
     * @param  string  $className
     * @param  string  $methodName
     * @return integer
     * @since  Method available since Release 3.6.0
     */
    public static function getSize($className, $methodName)
    {
        $groups = array_flip(self::getGroups($className, $methodName));
        $size   = self::SMALL;
        $class  = new ReflectionClass($className);

        if ((class_exists('PHPUnit_Extensions_Database_TestCase', false) &&
             $class->isSubclassOf('PHPUnit_Extensions_Database_TestCase')) ||
            (class_exists('PHPUnit_Extensions_SeleniumTestCase', false) &&
             $class->isSubclassOf('PHPUnit_Extensions_SeleniumTestCase'))) {
            $size = self::LARGE;
        } elseif (isset($groups['medium'])) {
            $size = self::MEDIUM;
        } elseif (isset($groups['large'])) {
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
     * Returns the process isolation settings for a test.
     *
     * @param  string  $className
     * @param  string  $methodName
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
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the preserve global state settings for a test.
     *
     * @param  string  $className
     * @param  string  $methodName
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
     * @return array
     * @since  Method available since Release 4.0.8
     */
    public static function getHookMethods($className)
    {
        if (!class_exists($className, false)) {
            return self::emptyHookMethodsArray();
        }

        if (!isset(self::$hookMethods[$className])) {
            self::$hookMethods[$className] = self::emptyHookMethodsArray();

            try {
                $class = new ReflectionClass($className);

                foreach ($class->getMethods() as $method) {
                    if ($method->getDeclaringClass()->getName() != $className) {
                        continue;
                    }

                    if (self::isBeforeClassMethod($method)) {
                        self::$hookMethods[$className]['beforeClass'][] = $method->getName();
                    }

                    if (self::isBeforeMethod($method)) {
                        self::$hookMethods[$className]['before'][] = $method->getName();
                    }

                    if (self::isAfterMethod($method)) {
                        self::$hookMethods[$className]['after'][] = $method->getName();
                    }

                    if (self::isAfterClassMethod($method)) {
                        self::$hookMethods[$className]['afterClass'][] = $method->getName();
                    }
                }
            } catch (ReflectionException $e) {
            }
        }

        return self::$hookMethods[$className];
    }

    /**
     * @return array
     * @since  Method available since Release 4.0.9
     */
    private static function emptyHookMethodsArray()
    {
        return array(
            'beforeClass' => array('setUpBeforeClass'),
            'before' => array('setUp'),
            'after' => array('tearDown'),
            'afterClass' => array('tearDownAfterClass')
        );
    }

    /**
     * @param  string  $className
     * @param  string  $methodName
     * @param  string  $settingName
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    private static function getBooleanAnnotationSetting($className, $methodName, $settingName)
    {
        $annotations = self::parseTestMethodAnnotations(
          $className, $methodName
        );

        $result = null;

        if (isset($annotations['class'][$settingName])) {
            if ($annotations['class'][$settingName][0] == 'enabled') {
                $result = true;
            } elseif ($annotations['class'][$settingName][0] == 'disabled') {
                $result = false;
            }
        }

        if (isset($annotations['method'][$settingName])) {
            if ($annotations['method'][$settingName][0] == 'enabled') {
                $result = true;
            } elseif ($annotations['method'][$settingName][0] == 'disabled') {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param  string $element
     * @return array
     * @throws PHPUnit_Framework_InvalidCoversTargetException
     * @since  Method available since Release 4.0.0
     */
    private static function resolveElementToReflectionObjects($element)
    {
        $codeToCoverList = array();

        if (strpos($element, '::') !== false) {
            list($className, $methodName) = explode('::', $element);

            if (isset($methodName[0]) && $methodName[0] == '<') {
                $classes = array($className);

                foreach ($classes as $className) {
                    if (!class_exists($className) &&
                        !interface_exists($className)) {
                        throw new PHPUnit_Framework_InvalidCoversTargetException(
                          sprintf(
                            'Trying to @cover or @use not existing class or ' .
                            'interface "%s".',
                            $className
                          )
                        );
                    }

                    $class   = new ReflectionClass($className);
                    $methods = $class->getMethods();
                    $inverse = isset($methodName[1]) && $methodName[1] == '!';

                    if (strpos($methodName, 'protected')) {
                        $visibility = 'isProtected';
                    } elseif (strpos($methodName, 'private')) {
                        $visibility = 'isPrivate';
                    } elseif (strpos($methodName, 'public')) {
                        $visibility = 'isPublic';
                    }

                    foreach ($methods as $method) {
                        if ($inverse && !$method->$visibility()) {
                            $codeToCoverList[] = $method;
                        } elseif (!$inverse && $method->$visibility()) {
                            $codeToCoverList[] = $method;
                        }
                    }
                }
            } else {
                $classes = array($className);

                foreach ($classes as $className) {
                    if ($className == '' && function_exists($methodName)) {
                        $codeToCoverList[] = new ReflectionFunction(
                          $methodName
                        );
                    } else {
                        if (!((class_exists($className) ||
                               interface_exists($className) ||
                               trait_exists($className)) &&
                              method_exists($className, $methodName))) {
                            throw new PHPUnit_Framework_InvalidCoversTargetException(
                              sprintf(
                                'Trying to @cover or @use not existing method "%s::%s".',
                                $className,
                                $methodName
                              )
                            );
                        }

                        $codeToCoverList[] = new ReflectionMethod(
                          $className, $methodName
                        );
                    }
                }
            }
        } else {
            $extended = false;

            if (strpos($element, '<extended>') !== false) {
                $element = str_replace(
                  '<extended>', '', $element
                );

                $extended = true;
            }

            $classes = array($element);

            if ($extended) {
                $classes = array_merge(
                  $classes,
                  class_implements($element),
                  class_parents($element)
                );
            }

            foreach ($classes as $className) {
                if (!class_exists($className) &&
                    !interface_exists($className) &&
                    !trait_exists($className)) {
                    throw new PHPUnit_Framework_InvalidCoversTargetException(
                      sprintf(
                        'Trying to @cover or @use not existing class or ' .
                        'interface "%s".',
                        $className
                      )
                    );
                }

                $codeToCoverList[] = new ReflectionClass($className);
            }
        }

        return $codeToCoverList;
    }

    /**
     * @param  array $reflectors
     * @return array
     */
    private static function resolveReflectionObjectsToLines(array $reflectors)
    {
        $result = array();

        foreach ($reflectors as $reflector) {
            $filename = $reflector->getFileName();

            if (!isset($result[$filename])) {
                $result[$filename] = array();
            }

            $result[$filename] = array_unique(
              array_merge(
                $result[$filename],
                range(
                  $reflector->getStartLine(), $reflector->getEndLine()
                )
              )
            );
        }

        return $result;
    }

    /**
     * @param  ReflectionClass $class
     * @param  ReflectionMethod $method
     * @return string
     */
    private static function getDocCommentsOfTestClassAndTestMethodAndTemplateMethods(ReflectionClass $class, ReflectionMethod $method)
    {
        $buffer = substr($class->getDocComment(),  3, -2) . PHP_EOL .
                  substr($method->getDocComment(), 3, -2);

        foreach (self::$templateMethods as $templateMethod) {
            if ($class->hasMethod($templateMethod)) {
                $_method = $class->getMethod($templateMethod);
                $buffer .= PHP_EOL . substr($_method->getDocComment(), 3, -2);
            }
        }

        return $buffer;
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     * @since  Method available since Release 4.0.8
     */
    private static function isBeforeClassMethod(ReflectionMethod $method)
    {
        return $method->isStatic() && strpos($method->getDocComment(), '@beforeClass') !== false;
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     * @since  Method available since Release 4.0.8
     */
    private static function isBeforeMethod(ReflectionMethod $method)
    {
        return preg_match('/@before\b/', $method->getDocComment());
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     * @since  Method available since Release 4.0.8
     */
    private static function isAfterClassMethod(ReflectionMethod $method)
    {
        return $method->isStatic() && strpos($method->getDocComment(), '@afterClass') !== false;
    }

    /**
     * @param  ReflectionMethod $method
     * @return boolean
     * @since  Method available since Release 4.0.8
     */
    private static function isAfterMethod(ReflectionMethod $method)
    {
        return preg_match('/@after\b/', $method->getDocComment());
    }
}
