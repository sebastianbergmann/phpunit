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
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Test helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_Test
{
    const REGEX_COVERS                   = '/@covers[\s]+([\!<>\:\.\w]+)([\s]+<extended>)?/';
    const REGEX_DATA_PROVIDER            = '/@dataProvider\s+([a-zA-Z0-9._:-\\\]+)/';
    const REGEX_EXPECTED_EXCEPTION       = '(@expectedException\s+([:.\w\\\]+)(?:[\t ]+(\S*))?(?:[\t ]+(\S*))?\s*$)m';

    private static $annotationCache = array();

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
     * @param  PHPUnit_Framework_Test       $test
     * @param  PHPUnit_Framework_TestResult $result
     * @return mixed
     */
    public static function lookupResult(PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result)
    {
        $testName = self::describe($test);

        foreach ($result->errors() as $error) {
            if ($testName == self::describe($error->failedTest())) {
                return $error;
            }
        }

        foreach ($result->failures() as $failure) {
            if ($testName == self::describe($failure->failedTest())) {
                return $failure;
            }
        }

        foreach ($result->notImplemented() as $notImplemented) {
            if ($testName == self::describe($notImplemented->failedTest())) {
                return $notImplemented;
            }
        }

        foreach ($result->skipped() as $skipped) {
            if ($testName == self::describe($skipped->failedTest())) {
                return $skipped;
            }
        }

        return PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
    }

    /**
     * Returns the files and lines a test method wants to cover.
     *
     * @param  string $className
     * @param  string $methodName
     * @return array
     * @since  Method available since Release 3.2.0
     */
    public static function getLinesToBeCovered($className, $methodName)
    {
        $result = array();
        $codeToCoverList = array();

        if (($pos = strpos($methodName, ' ')) !== FALSE) {
            $methodName = substr($methodName, 0, $pos);
        }

        try {
            $class      = new ReflectionClass($className);
            $method     = new ReflectionMethod($className, $methodName);
            $docComment = $class->getDocComment() . $method->getDocComment();

            foreach (array('setUp', 'assertPreConditions', 'assertPostConditions', 'tearDown') as $templateMethod) {
                if ($class->hasMethod($templateMethod)) {
                    $reflector = $class->getMethod($templateMethod);
                    $docComment .= $reflector->getDocComment();
                    unset($reflector);
                }
            }

            if (preg_match_all(self::REGEX_COVERS, $docComment, $matches)) {
                foreach ($matches[1] as $i => $method) {
                    $codeToCoverList = array_merge(
                        $codeToCoverList,
                        self::resolveCoversToReflectionObjects($method, !empty($matches[2][$i]))
                    );
                }

                foreach ($codeToCoverList as $codeToCover) {
                    $fileName  = $codeToCover->getFileName();
                    $startLine = $codeToCover->getStartLine();
                    $endLine   = $codeToCover->getEndLine();

                    if (!isset($result[$fileName])) {
                        $result[$fileName] = array();
                    }

                    $result[$fileName] = array_unique(
                      array_merge($result[$fileName], range($startLine, $endLine))
                    );
                }
            }
        }

        catch (ReflectionException $e) {
        }

        return $result;
    }

    /**
     * Returns the expected exception for a test.
     *
     * @param  string $docComment
     * @return array
     * @since  Method available since Release 3.3.6
     */
    public static function getExpectedException($docComment)
    {
        if (preg_match(self::REGEX_EXPECTED_EXCEPTION, $docComment, $matches)) {
            $class   = $matches[1];
            $code    = 0;
            $message = '';

            if (isset($matches[2])) {
                $message = trim($matches[2]);
            }

            if (isset($matches[3])) {
                $code = (int)$matches[3];
            }

            return array(
              'class' => $class, 'code' => $code, 'message' => $message
            );
        }

        return FALSE;
    }

    /**
     * Returns the provided data for a method.
     *
     * @param  string $className
     * @param  string $methodName
     * @param  string $docComment
     * @return array
     * @throws ReflectionException
     * @since  Method available since Release 3.2.0
     */
    public static function getProvidedData($className, $methodName)
    {
        $reflector  = new ReflectionMethod($className, $methodName);
        $docComment = $reflector->getDocComment();

        if (preg_match(self::REGEX_DATA_PROVIDER, $docComment, $matches)) {
            try {
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
                    return $dataProviderMethod->invoke($object);
                } else {
                    return $dataProviderMethod->invoke($object, $methodName);
                }
            }

            catch (ReflectionException $e) {
            }
        }
    }

    /**
     * Returns the files and lines a test method wants to cover.
     *
     * @param  string  $method
     * @param  boolean $extended
     * @return array
     * @since  Method available since Release 3.3.0
     */
    private static function resolveCoversToReflectionObjects($method, $extended)
    {
        $codeToCoverList = array();

        if (strpos($method, '::') !== FALSE) {
            list($className, $methodName) = explode('::', $method);

            if ($methodName{0} == '<') {
                $classes = array($className);

                if ($extended) {
                    $classes = array_merge(
                      $classes,
                      class_implements($className),
                      class_parents($className)
                    );
                }

                foreach ($classes as $className)
                {
                    $class   = new ReflectionClass($className);
                    $methods = $class->getMethods();
                    $inverse = isset($methodName{1}) && $methodName{1} == '!';

                    if (strpos($methodName, 'protected')) {
                        $visibility = 'isProtected';
                    }

                    else if (strpos($methodName, 'private')) {
                        $visibility = 'isPrivate';
                    }

                    else if (strpos($methodName, 'public')) {
                        $visibility = 'isPublic';
                    }

                    foreach ($methods as $method) {
                        if ($inverse && !$method->$visibility()) {
                            $codeToCoverList[] = $method;
                        }

                        else if (!$inverse && $method->$visibility()) {
                            $codeToCoverList[] = $method;
                        }
                    }
                }
            } else {
                $classes = array($className);

                if ($extended) {
                    $classes = array_merge($classes, class_parents($className));
                }

                foreach ($classes as $className) {
                    $codeToCoverList[] = new ReflectionMethod($className, $methodName);
                }
            }
        } else {
            $classes = array($method);

            if ($extended) {
                $classes = array_merge(
                  $classes,
                  class_implements($method),
                  class_parents($method)
                );
            }

            foreach ($classes as $className) {
                $codeToCoverList[] = new ReflectionClass($className);
            }
        }

        return $codeToCoverList;
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
            $method = new ReflectionMethod($className, $methodName);
            self::$annotationCache[$className . '::' . $methodName] = self::parseAnnotations($method->getDocComment());
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

        if (preg_match_all('(@(?P<name>[A-Za-z_-]+)\s+(?P<value>.*?)\s*$)m', $docblock, $matches)) {
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
        $annotations  = self::parseTestMethodAnnotations($className, $methodName);
        $dependencies = array();

        if (isset($annotations['class']['depends'])) {
            $dependencies = $annotations['class']['depends'];
        }

        if (isset($annotations['method']['depends'])) {
            $dependencies = array_merge($dependencies, $annotations['method']['depends']);
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
        $annotations = self::parseTestMethodAnnotations($className, $methodName);
        $groups      = array();

        if (isset($annotations['class']['group'])) {
            $groups = $annotations['class']['group'];
        }

        if (isset($annotations['method']['group'])) {
            $groups = array_merge($groups, $annotations['method']['group']);
        }

        return array_unique($groups);
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
        $annotations = self::parseTestMethodAnnotations($className, $methodName);
        $tickets     = array();

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
     * @param  string $className
     * @param  string $methodName
     * @param  string $settingName
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    private static function getBooleanAnnotationSetting($className, $methodName, $settingName)
    {
        $annotations = self::parseTestMethodAnnotations($className, $methodName);
        $result      = NULL;

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
?>
