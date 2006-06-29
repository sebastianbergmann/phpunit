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
 * @version    CVS: $Id: IncludePathTestCollector.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.1.0
 */

if (!class_exists('AppendIterator')) {
    class AppendIterator implements Iterator {
        private $iterators;
    
        public function __construct() {
            $this->iterators = new ArrayIterator();
        }
    
        public function __call($func, $params) {
            return call_user_func_array(array($this->getInnerIterator(), $func), $params);
        }

        public function append(Iterator $it) {
            $this->iterators->append($it);
        }
    
        public function getInnerIterator() {
            return $this->iterators->current();
        }
    
        public function rewind() {
            $this->iterators->rewind();

            if ($this->iterators->valid()) {
                $this->getInnerIterator()->rewind();
            }
        }
        
        public function valid() {
            return $this->iterators->valid() && $this->getInnerIterator()->valid();
        }
        
        public function current() {
            return $this->iterators->valid() ? $this->getInnerIterator()->current() : NULL;
        }
        
        public function key() {
            return $this->iterators->valid() ? $this->getInnerIterator()->key() : NULL;
        }
        
        public function next() {
            if (!$this->iterators->valid()) return;
            $this->getInnerIterator()->next();

            if ($this->getInnerIterator()->valid()) return;
            $this->iterators->next();

            while ($this->iterators->valid()) {
                $this->getInnerIterator()->rewind();

                if ($this->getInnerIterator()->valid()) return;
                $this->iterators->next();
            }
        }
    }
}

require_once 'PHPUnit2/Runner/TestCollector.php';

require_once 'PEAR/Config.php';

/**
 * An implementation of a TestCollector that consults the
 * include path set in the php.ini. 
 *
 * @category   Testing
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      Class available since Release 2.1.0
 */

class PHPUnit2_Runner_IncludePathTestCollector implements PHPUnit2_Runner_TestCollector {
    /**
     * @return array
     * @access public
     */
    public function collectTests() {
        $config   = new PEAR_Config;
        $iterator = new AppendIterator;
        $result   = array();

        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $delimiter = ';';
        } else {
            $delimiter = ':';
        }

        $paths   = explode($delimiter, ini_get('include_path'));
        $paths[] = $config->get('test_dir');

        foreach ($paths as $path) {
            $iterator->append(
              new RecursiveIteratorIterator(
                  new RecursiveDirectoryIterator($path)
              )
            );
        }

        foreach ($iterator as $path => $file) {
            if ($this->isTestClass($file)) {
                if (substr(PHP_OS, 0, 3) == 'WIN') {
                    $path = str_replace('/', '\\', $path);
                }

                $result[] = $path;
            }
        }

        return $result;
    }

    /**
     * Considers a file to contain a test class when it contains the
     * pattern "Test" in its name and its name ends with ".php".
     *
     * @param  string  $classFileName
     * @return boolean
     * @access protected
     */
    protected function isTestClass($classFileName) {
        return (strpos($classFileName, 'Test') !== FALSE && substr($classFileName, -4) == '.php') ? TRUE : FALSE;
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
