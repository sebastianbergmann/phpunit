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
// $Id: Iterator.php 539 2006-02-13 16:08:42Z sb $
//

/**
 * @author      Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright   Copyright &copy; 2002-2005 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license     http://www.php.net/license/3_0.txt The PHP License, Version 3.0
 * @category    Testing
 * @package     PHPUnit2
 * @subpackage  Tests
 */
class PHPUnit2_Tests_Iterator implements Iterator {
    private $array;
    private $position;
  
    public function __construct($array = array()) {
        $this->array = $array;
    }
  
    public function rewind() {
        $this->position = 0;
    }
  
    public function valid() {
        return $this->position < sizeof($this->array);
    }
  
    public function key() {
        return $this->position;
    }
  
    public function current() {
        return $this->array[$this->position];
    }
  
    public function next() {
        $this->position++;
    }
}

/*
 * vim600:  et sw=2 ts=2 fdm=marker
 * vim<600: et sw=2 ts=2
 */
?>
