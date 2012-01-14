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
 * @author     Sam Smith <samuel.david.smith@gmail.com>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release
 */

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @package    PHPUnit
 * @author     Sam Smith <samuel.david.smith@gmail.com>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release
 */
class Util_AutoloaderTest extends PHPUnit_Framework_TestCase
{
	protected $autoloader;

	public function setUp() {
		$_filesPath = dirname(__FILE__) . '/../_files';

		$this->autoloader = new PHPUnit_Util_Autoloader();
		$this->autoloader->addPrefix('Prefix', $_filesPath);
		$this->autoloader->addNamespace('NamespaceTest', $_filesPath); 
	}

	public function testUnconfiguredAutoloadCantLoadAnything() {
		$autoloader = new PHPUnit_Util_Autoloader();

		$this->assertFalse($autoloader->autoload('PHPUnit_Util_Autoloader'));
	}

	/**
	 * @expectedException PHPUnit_Util_Autoloader_Exception
	 */
	public function testAddNamespaceThrowsWhenDirectoryDoesntExist() {
		$this->autoloader->addNamespace('PHPUnit', dirname(__FILE__) . '/non_existant_directory');
	}

	/**
	 * @expectedException PHPUnit_Util_Autoloader_Exception
	 */
	public function testAddPrefixThrowsWhenDirectoryDoesntExist() {
		$this->autoloader->addPrefix('PHPUnit', dirname(__FILE__) . '/non_existant_directory');
	}

	public static function autoloadProvider() {
		return array(
			array('Prefix_Class'),
			array('\\NamespaceTest\\ClassTest'),
			array('\\NamespaceTest\\Class_Two')
		);
	}

	/**
	 * @dataProvider autoloadProvider
	 */
	public function testAutoload($class) {
		$this->assertTrue($this->autoloader->autoload($class));
		$this->assertTrue(class_exists($class), true);
	}
}
