<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2 :: TestDox                                            |
// +------------------------------------------------------------------------+
// | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//
// $Id: NamePrettifier.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * A prettifier for class names.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Extensions
 * @since       2.1.0
 */
class PHPUnit2_Extensions_TestDox_NamePrettifier {
    // {{{ Members

    /**
    * @var    string
    * @access protected
    */
    protected $prefix = 'Test';

    /**
    * @var    string
    * @access protected
    */
    protected $suffix = 'Test';

    // }}}
    // {{{ public function isATestMethod($testMethodName)

    /**
    * Tests if a method is a test method.
    *
    * @param  string  $testMethodName
    * @return boolean
    * @access public
    */
    public function isATestMethod($testMethodName) {
        if (substr($testMethodName, 0, 4) == 'test') {
            return TRUE;
        }

        return FALSE;
    }

    // }}}
    // {{{ public function prettifyTestClass($testClassName)

    /**
    * Prettifies the name of a test class.
    *
    * @param  string  $testClassName
    * @return string
    * @access public
    */
    public function prettifyTestClass($testClassName) {
        $title = $testClassName;

        if ($this->suffix !== NULL &&
            $this->suffix == substr($testClassName, -1 * strlen($this->suffix))) {
            $title = substr($title, 0, strripos($title, $this->suffix));
        }

        if ($this->prefix !== NULL &&
            $this->prefix == substr($testClassName, 0, strlen($this->prefix))) {
            $title = substr($title, strlen($this->prefix));
        }

        return $title;
    }

    // }}}
    // {{{ public function prettifyTestMethod($testMethodName)

    /**
    * Prettifies the name of a test method.
    *
    * @param  string  $testMethodName
    * @return string
    * @access public
    */
    public function prettifyTestMethod($testMethodName) {
        $buffer = '';

        for ($i = 4; $i < strlen($testMethodName); $i++) {
            if ($i > 4 &&
                ord($testMethodName[$i]) >= 65 && 
                ord($testMethodName[$i]) <= 90) {
                $buffer .= ' ' . strtolower($testMethodName[$i]);
            } else {
                $buffer .= $testMethodName[$i];
            }
        }

        return $buffer;
    }

    // }}}
    // {{{ public function setPrefix($prefix)

    /**
    * Sets the prefix of test names.
    *
    * @param  string  $prefix
    * @access public
    */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    // }}}
    // {{{ public function setSuffix($suffix)

    /**
    * Sets the suffix of test names.
    *
    * @param  string  $prefix
    * @access public
    */
    public function setSuffix($suffix) {
        $this->suffix = $suffix;
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
