<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2012, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

/**
 *
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Util_GlobalState
{
    /**
     * @var array
     */
    protected static $globals = array();

    /**
     * @var array
     */
    protected static $staticAttributes = array();

    /**
     * @var array
     */
    protected static $superGlobalArrays = array(
      '_ENV',
      '_POST',
      '_GET',
      '_COOKIE',
      '_SERVER',
      '_FILES',
      '_REQUEST'
    );

    /**
     * @var array
     */
    protected static $superGlobalArraysLong = array(
      'HTTP_ENV_VARS',
      'HTTP_POST_VARS',
      'HTTP_GET_VARS',
      'HTTP_COOKIE_VARS',
      'HTTP_SERVER_VARS',
      'HTTP_POST_FILES'
    );

    /**
     * @var array
     */
    protected static $phpunitFiles;

    public static function backupGlobals(array $blacklist)
    {
        self::$globals     = array();
        $superGlobalArrays = self::getSuperGlobalArrays();

        foreach ($superGlobalArrays as $superGlobalArray) {
            if (!in_array($superGlobalArray, $blacklist)) {
                self::backupSuperGlobalArray($superGlobalArray);
            }
        }

        foreach (array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' &&
                !in_array($key, $superGlobalArrays) &&
                !in_array($key, $blacklist) &&
                !$GLOBALS[$key] instanceof Closure) {
                self::$globals['GLOBALS'][$key] = serialize($GLOBALS[$key]);
            }
        }
    }

    public static function restoreGlobals(array $blacklist)
    {
        if (ini_get('register_long_arrays') == '1') {
            $superGlobalArrays = array_merge(
              self::$superGlobalArrays, self::$superGlobalArraysLong
            );
        } else {
            $superGlobalArrays = self::$superGlobalArrays;
        }

        foreach ($superGlobalArrays as $superGlobalArray) {
            if (!in_array($superGlobalArray, $blacklist)) {
                self::restoreSuperGlobalArray($superGlobalArray);
            }
        }

        foreach (array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' &&
                !in_array($key, $superGlobalArrays) &&
                !in_array($key, $blacklist)) {
                if (isset(self::$globals['GLOBALS'][$key])) {
                    $GLOBALS[$key] = unserialize(
                      self::$globals['GLOBALS'][$key]
                    );
                } else {
                    unset($GLOBALS[$key]);
                }
            }
        }

        self::$globals = array();
    }

    protected static function backupSuperGlobalArray($superGlobalArray)
    {
        self::$globals[$superGlobalArray] = array();

        if (isset($GLOBALS[$superGlobalArray]) &&
            is_array($GLOBALS[$superGlobalArray])) {
            foreach ($GLOBALS[$superGlobalArray] as $key => $value) {
                self::$globals[$superGlobalArray][$key] = serialize($value);
            }
        }
    }

    protected static function restoreSuperGlobalArray($superGlobalArray)
    {
        if (isset($GLOBALS[$superGlobalArray]) &&
            is_array($GLOBALS[$superGlobalArray]) &&
            isset(self::$globals[$superGlobalArray])) {
            $keys = array_keys(
              array_merge(
                $GLOBALS[$superGlobalArray], self::$globals[$superGlobalArray]
              )
            );

            foreach ($keys as $key) {
                if (isset(self::$globals[$superGlobalArray][$key])) {
                    $GLOBALS[$superGlobalArray][$key] = unserialize(
                      self::$globals[$superGlobalArray][$key]
                    );
                } else {
                    unset($GLOBALS[$superGlobalArray][$key]);
                }
            }
        }

        self::$globals[$superGlobalArray] = array();
    }

    public static function getIncludedFilesAsString()
    {
        $blacklist = self::phpunitFiles();
        $files     = get_included_files();
        $result    = '';

        for ($i = count($files) - 1; $i > 0; $i--) {
            if (!isset($blacklist[$files[$i]]) && is_file($files[$i])) {
                $result = 'require_once \'' . $files[$i] . "';\n" . $result;
            }
        }

        return $result;
    }

    public static function getConstantsAsString()
    {
        $constants = get_defined_constants(TRUE);
        $result    = '';

        if (isset($constants['user'])) {
            foreach ($constants['user'] as $name => $value) {
                $result .= sprintf(
                  'if (!defined(\'%s\')) define(\'%s\', %s);' . "\n",
                  $name,
                  $name,
                  self::exportVariable($value)
                );
            }
        }

        return $result;
    }

    public static function getGlobalsAsString()
    {
        $result            = '';
        $superGlobalArrays = self::getSuperGlobalArrays();

        foreach ($superGlobalArrays as $superGlobalArray) {
            if (isset($GLOBALS[$superGlobalArray]) &&
                is_array($GLOBALS[$superGlobalArray])) {
                foreach (array_keys($GLOBALS[$superGlobalArray]) as $key) {
                    if ($GLOBALS[$superGlobalArray][$key] instanceof Closure) {
                        continue;
                    }

                    $result .= sprintf(
                      '$GLOBALS[\'%s\'][\'%s\'] = %s;' . "\n",
                      $superGlobalArray,
                      $key,
                      self::exportVariable($GLOBALS[$superGlobalArray][$key])
                    );
                }
            }
        }

        $blacklist   = $superGlobalArrays;
        $blacklist[] = 'GLOBALS';
        $blacklist[] = '_PEAR_Config_instance';

        foreach (array_keys($GLOBALS) as $key) {
            if (!in_array($key, $blacklist) && !$GLOBALS[$key] instanceof Closure) {
                $result .= sprintf(
                  '$GLOBALS[\'%s\'] = %s;' . "\n",
                  $key,
                  self::exportVariable($GLOBALS[$key])
                );
            }
        }

        return $result;
    }

    protected static function getSuperGlobalArrays()
    {
        if (ini_get('register_long_arrays') == '1') {
            return array_merge(
              self::$superGlobalArrays, self::$superGlobalArraysLong
            );
        } else {
            return self::$superGlobalArrays;
        }
    }

    public static function backupStaticAttributes(array $blacklist)
    {
        self::$staticAttributes = array();
        $declaredClasses        = get_declared_classes();
        $declaredClassesNum     = count($declaredClasses);

        for ($i = $declaredClassesNum - 1; $i >= 0; $i--) {
            if (strpos($declaredClasses[$i], 'PHPUnit') !== 0 &&
                strpos($declaredClasses[$i], 'File_Iterator') !== 0 &&
                strpos($declaredClasses[$i], 'PHP_CodeCoverage') !== 0 &&
                strpos($declaredClasses[$i], 'PHP_Invoker') !== 0 &&
                strpos($declaredClasses[$i], 'PHP_Timer') !== 0 &&
                strpos($declaredClasses[$i], 'PHP_TokenStream') !== 0 &&
                strpos($declaredClasses[$i], 'sfYaml') !== 0 &&
                strpos($declaredClasses[$i], 'Text_Template') !== 0 &&
                !$declaredClasses[$i] instanceof PHPUnit_Framework_Test) {
                $class = new ReflectionClass($declaredClasses[$i]);

                if (!$class->isUserDefined()) {
                    break;
                }

                $backup = array();

                foreach ($class->getProperties() as $attribute) {
                    if ($attribute->isStatic()) {
                        $name = $attribute->getName();

                        if (!isset($blacklist[$declaredClasses[$i]]) ||
                           !in_array($name, $blacklist[$declaredClasses[$i]])) {
                            $attribute->setAccessible(TRUE);
                            $value = $attribute->getValue();

                            if (!$value instanceof Closure) {
                                $backup[$name] = serialize($value);
                            }
                        }
                    }
                }

                if (!empty($backup)) {
                    self::$staticAttributes[$declaredClasses[$i]] = $backup;
                }
            }
        }
    }

    public static function restoreStaticAttributes()
    {
        foreach (self::$staticAttributes as $className => $staticAttributes) {
            foreach ($staticAttributes as $name => $value) {
                $reflector = new ReflectionProperty($className, $name);
                $reflector->setAccessible(TRUE);
                $reflector->setValue(unserialize($value));
            }
        }

        self::$staticAttributes = array();
    }

    protected static function exportVariable($variable)
    {
        if (is_scalar($variable) || is_null($variable) ||
           (is_array($variable) && self::arrayOnlyContainsScalars($variable))) {
            return var_export($variable, TRUE);
        }

        return 'unserialize(\'' .
                str_replace("'", "\'", serialize($variable)) .
                '\')';
    }

    protected static function arrayOnlyContainsScalars(array $array)
    {
        $result = TRUE;

        foreach ($array as $element) {
            if (is_array($element)) {
                $result = self::arrayOnlyContainsScalars($element);
            }

            else if (!is_scalar($element) && !is_null($element)) {
                $result = FALSE;
            }

            if ($result === FALSE) {
                break;
            }
        }

        return $result;
    }

    /**
     * @return array
     * @since  Method available since Release 3.6.0
     */
    public static function phpunitFiles()
    {
        if (self::$phpunitFiles === NULL) {
            self::$phpunitFiles = phpunit_autoload();

            if (function_exists('phpunit_mockobject_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, phpunit_mockobject_autoload()
                );
            }

            if (function_exists('file_iterator_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, file_iterator_autoload()
                );
            }

            if (function_exists('php_codecoverage_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, php_codecoverage_autoload()
                );
            }

            if (function_exists('php_timer_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, php_timer_autoload()
                );
            }

            if (function_exists('php_tokenstream_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, php_tokenstream_autoload()
                );
            }

            if (function_exists('text_template_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, text_template_autoload()
                );
            }

            if (function_exists('phpunit_dbunit_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, phpunit_dbunit_autoload()
                );
            }

            if (function_exists('phpunit_selenium_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, phpunit_selenium_autoload()
                );
            }

            if (function_exists('phpunit_story_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, phpunit_story_autoload()
                );
            }

            if (function_exists('php_invoker_autoload')) {
                self::$phpunitFiles = array_merge(
                  self::$phpunitFiles, php_invoker_autoload()
                );
            }

            foreach (self::$phpunitFiles as $key => $value) {
                self::$phpunitFiles[$key] = str_replace(
                  '/', DIRECTORY_SEPARATOR, $value
                );
            }

            self::$phpunitFiles = array_flip(self::$phpunitFiles);
        }

        return self::$phpunitFiles;
    }
}
