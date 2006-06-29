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
 * @version    CVS: $Id: NamePrettifier.php 539 2006-02-13 16:08:42Z sb $
 * @link       http://pear.php.net/package/PHPUnit2
 * @since      File available since Release 2.3.0
 */

/**
 * Prettifies class and method names for use in TestDox documentation.
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
class PHPUnit2_Util_TestDox_NamePrettifier {
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

    /**
     * Prettifies the name of a test method.
     *
     * @param  string  $testMethodName
     * @return string
     * @access public
     */
    public function prettifyTestMethod($testMethodName) {
        $buffer = '';

        $testMethodName = preg_replace('#\d+$#', '', $testMethodName);

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

    /**
     * Sets the prefix of test names.
     *
     * @param  string  $prefix
     * @access public
     */
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    /**
     * Sets the suffix of test names.
     *
     * @param  string  $prefix
     * @access public
     */
    public function setSuffix($suffix) {
        $this->suffix = $suffix;
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
