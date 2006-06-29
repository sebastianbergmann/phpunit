<?php
//
// +------------------------------------------------------------------------+
// | PEAR :: PHPUnit2                                                       |
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
// $Id: Version.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * This class defines the current version of PHPUnit.
 *
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    PHP
 * @package     PHPUnit2
 * @subpackage  Runner
 */
class PHPUnit2_Runner_Version {
    // {{{ public static function id()

    /**
    * Returns the current version of PHPUnit.
    *
    * @return string
    * @access public
    * @static
    */
  	public static function id() {
    		return '@version@';
  	}

    // }}}
    // {{{ public function getVersionString()

    /**
    * @return string
    * @access public
    * @static
    */
    public static function getVersionString() {
        return "PHPUnit @version@ by Sebastian Bergmann.";
    }

    // }}}
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
