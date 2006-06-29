<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: IncludePathTestCollector.php 539 2006-02-13 16:08:42Z sb $
//

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

/**
 * An implementation of a TestCollector that consults the
 * include path set in the php.ini. 
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Runner
 * @since       2.1.0
 */
class PHPUnit2_Runner_IncludePathTestCollector implements PHPUnit2_Runner_TestCollector {
    // {{{ public function collectTests()

    /**
    * @return array
    * @access public
    */
    public function collectTests() {
        $iterator = new AppendIterator;
        $result   = array();

        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $delimiter = ';';
        } else {
            $delimiter = ':';
        }

        $paths = explode($delimiter, ini_get('include_path'));

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

    // }}}
    // {{{ protected function isTestClass($classFileName)

    /**
    * Considers a file to contain a test class when it contains the
    * pattern "Test" in its name and its name ends with ".php".
    *
    * @param  string  $classFileName
    * @return boolean
    * @access public
    */
    protected function isTestClass($classFileName) {
        return (strpos($classFileName, 'Test') !== FALSE && substr($classFileName, -4) == '.php') ? TRUE : FALSE;
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
