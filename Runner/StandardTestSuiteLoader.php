<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: StandardTestSuiteLoader.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.0.0
 */

require_once 'PHPUnit2/Runner/TestSuiteLoader.php';
require_once 'PHPUnit2/Util/Fileloader.php';

require_once 'PEAR/Config.php';

/**
 * The standard test suite loader.
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.0.0
 */
class PHPUnit2_Runner_StandardTestSuiteLoader implements PHPUnit2_Runner_TestSuiteLoader {
    /**
     * @param  string  $suiteClassName
     * @param  string  $suiteClassFile
     * @return ReflectionClass
     * @throws Exception
     * @access public
     */
    public function load($suiteClassName, $suiteClassFile = '') {
        $suiteClassName = str_replace('.php', '', $suiteClassName);
        $suiteClassFile = !empty($suiteClassFile) ? $suiteClassFile : str_replace('_', '/', $suiteClassName) . '.php';

        if (!class_exists($suiteClassName)) {
            if(!file_exists($suiteClassFile)) {
                $config = new PEAR_Config;

                $includePaths   = explode(PATH_SEPARATOR, get_include_path());
                $includePaths[] = $config->get('test_dir');

                foreach ($includePaths as $includePath) {
                    $file = $includePath . DIRECTORY_SEPARATOR . $suiteClassFile;

                    if (file_exists($file)) {
                        $suiteClassFile = $file;
                        break;
                    }
                }
            }

            PHPUnit2_Util_Fileloader::checkAndLoad($suiteClassFile);
        }

        if (class_exists($suiteClassName)) {
            return new ReflectionClass($suiteClassName);
        } else {
            throw new Exception(
              sprintf(
                'Class %s could not be found in %s.',

                $suiteClassName,
                $suiteClassFile
              )
            );
        }
    }

    /**
     * @param  ReflectionClass  $aClass
     * @return ReflectionClass
     * @access public
     */
    public function reload(ReflectionClass $aClass) {
        return $aClass;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
