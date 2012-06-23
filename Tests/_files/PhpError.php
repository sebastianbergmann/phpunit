<?php

class PhpErrorTestCase extends PHPUnit_Framework_TestCase {

  public function testError () {

    trigger_error ( 'Error message', E_USER_ERROR );
  }

  public function testNotice () {

    trigger_error ( 'Notice message', E_USER_NOTICE );
  }

  public function testDeprecated () {

    trigger_error ( 'Deprecated message', E_USER_DEPRECATED );
  }

  public function testWarning () {

    trigger_error ( 'Warning message', E_USER_WARNING );
  }
}
