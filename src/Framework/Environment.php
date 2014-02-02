<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * Provides facilities to check the current environment for particular features. Will
 * cache checks.
 * @package    PHPUnit
 * @author     Anthony Bishopric <phpunit@anthonybishopric.com>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */
class PHPUnit_Framework_Environment
{
	private static $isHHVM;
	private static $xdebugLoaded;
	private static $canInvokePHPSubprocess;

	public static function isHHVM() 
	{
		if (!isset(static::$isHHVM)) {
			static::$isHHVM = defined("HPHP_VERSION");
		}
		return static::$isHHVM;
	}

	public static function isXDebugLoaded() 
	{
		if (!isset(static::$xdebugLoaded)) {
			static::$xdebugLoaded = extension_loaded('xdebug');	
		}
		return static::$xdebugLoaded;
	}

	public static function canCollectCodeCoverage()
	{
		return static::isHHVM() || static::isXDebugLoaded();
	}

	public static function canInvokePHPSubprocess() 
	{
		if (!isset(static::$canInvokePHPSubprocess))
		{
			static::$canInvokePHPSubprocess = extension_loaded('pcntl') && class_exists('PHP_Invoker');
		}
		return static::$canInvokePHPSubprocess;
	}
}