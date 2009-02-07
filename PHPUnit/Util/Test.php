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
    const REGEX_BACKUP_GLOBALS           = '/@backupGlobals\s+([a-zA-Z0-9._-]+)/';
    const REGEX_BACKUP_STATIC_ATTRIBUTES = '/@backupStaticAttributes\s+([a-zA-Z0-9._-]+)/';
    const REGEX_COVERS                   = '/@covers[\s]+([\!<>\:\.\w]+)([\s]+<extended>)?/';
    const REGEX_DATA_PROVIDER            = '/@dataProvider\s+([a-zA-Z0-9._:-\\\]+)/';
    const REGEX_DEPENDS                  = '/@depends\s+([a-zA-Z0-9._:-\\\]+)/';
    const REGEX_EXPECTED_EXCEPTION       = '(@expectedException\s+([:.\w\\\]+)(?:[\t ]+(\S*))?(?:[\t ]+(\S*))?\s*$)m';
    const REGEX_GROUP                    = '/@group\s+([a-zA-Z0-9._-]+)/';
    const REGEX_USE_OUTPUT_BUFFERING     = '/@outputBuffering\s+([a-zA-Z0-9._-]+)/';

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
     * Returns the dependencies for a test class or method.
     *
     * @param  string $docComment
     * @param  array  $dependencies
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public static function getDependencies($docComment, array $dependencies = array())
    {
        if (preg_match_all(self::REGEX_DEPENDS, $docComment, $matches)) {
            $dependencies = array_unique(array_merge($dependencies, $matches[1]));
        }

        return $dependencies;
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
     * Returns the groups for a test class or method.
     *
     * @param  string $docComment
     * @param  array  $groups
     * @return array
     * @since  Method available since Release 3.2.0
     */
    public static function getGroups($docComment, array $groups = array())
    {
        if (preg_match_all(self::REGEX_GROUP, $docComment, $matches)) {
            $groups = array_unique(array_merge($groups, $matches[1]));
        }

        return $groups;
    }

    /**
     * Returns the provided data for a method.
     *
     * @param  string $className
     * @param  string $methodName
     * @param  string $docComment
     * @return array
     * @since  Method available since Release 3.2.0
     */
    public static function getProvidedData($className, $methodName, $docComment)
    {
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
     * Returns the backup settings for a test.
     *
     * @param  string $classDocComment
     * @param  string $methodDocComment
     * @return array
     * @since  Method available since Release 3.4.0
     */
    public static function getBackupSettings($classDocComment, $methodDocComment)
    {
        return array(
          'backupGlobals' => self::getSettings(
            $classDocComment, $methodDocComment, self::REGEX_BACKUP_GLOBALS
          ),
          'backupStaticAttributes' => self::getSettings(
            $classDocComment, $methodDocComment, self::REGEX_BACKUP_STATIC_ATTRIBUTES
          )
        );
    }

    /**
     * Returns the output buffering settings for a test.
     *
     * @param  string $classDocComment
     * @param  string $methodDocComment
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    public static function getOutputBufferingSettings($classDocComment, $methodDocComment)
    {
        return self::getSettings(
          $classDocComment, $methodDocComment, self::REGEX_USE_OUTPUT_BUFFERING
        );
    }

    /**
     * @param  string $classDocComment
     * @param  string $methodDocComment
     * @return boolean
     * @since  Method available since Release 3.4.0
     */
    private static function getSettings($classDocComment, $methodDocComment, $regex)
    {
        $result = NULL;

        if (preg_match($regex, $classDocComment, $matches)) {
            if ($matches[1] == 'enabled') {
                $result = TRUE;
            }

            else if ($matches[1] == 'disabled') {
                $result = FALSE;
            }
        }

        if (preg_match($regex, $methodDocComment, $matches)) {
            if ($matches[1] == 'enabled') {
                $result = TRUE;
            }

            else if ($matches[1] == 'disabled') {
                $result = FALSE;
            }
        }

        return $result;
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
                    $classes += class_implements($className);
                    $classes += class_parents($className);
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
                    $classes += class_parents($className);
                }

                foreach ($classes as $className) {
                    $codeToCoverList[] = new ReflectionMethod($className, $methodName);
                }
            }
        } else {
            $classes = array($method);

            if ($extended) {
                $classes += class_implements($method);
                $classes += class_parents($method);
            }

            foreach ($classes as $className) {
                $codeToCoverList[] = new ReflectionClass($className);
            }
        }

        return $codeToCoverList;
    }
}
?>
