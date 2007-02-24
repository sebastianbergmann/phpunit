<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHP Version 5
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @package    PHPUnit2
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    CVS: $Id: IncludePathTestCollector.php,v 1.13.2.5 2005/12/17 16:04:56 sebastian Exp $
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
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
