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
 * @subpackage Util
 * @author     Sam Smith <samuel.david.smith@gmail.com>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release
 */

/**
 * A PSR-0 compliant autoloader that can be configured in your PHPUnit
 * configuration to help make your bootstrap file smaller.
 *
 * @package    PHPUnit
 * @subpackage Util
 * @author     Sam Smith <samuel.david.smith@gmail.com>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release
 */
class PHPUnit_Util_Autoloader
{
	protected $isRegistered = FALSE;
	protected $namespaces   = array();
	protected $prefixes     = array();

	/**
	 * Loads the class given its name.
	 *
	 * @return TRUE if the class was loaded, otherwise FALSE
	 * @since  Method available since Release
	 */
	public function autoload($class) {
		$class = ltrim($class, '\\_');

        // Is the class namespaced?
        if (strpos($class, '\\') !== false) {
            $candidates = $this->namespaces;
            $separator = '\\';
        } else {
            $candidates = $this->prefixes;
            $separator = '_';
        }

        $index = substr($class, 0, strpos($class, $separator));

        if ( ! isset($candidates[$index])) {
            return FALSE;
        }

        $classPath = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class);

		foreach ($candidates[$index] as $directory) {
			$path = rtrim($directory, '\\/') . DIRECTORY_SEPARATOR . $classPath . '.php';

			if (is_file($path)) {
				require_once $path;

				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Registers the autoloader if it has not already been registered.
	 *
	 * @since Method available since Release
	 */
	public function register() {
		if ( ! $this->isRegistered) {
			spl_autoload_register(array($this, 'autoload'));

			$this->isRegistered = true;
		}
	}

	/**
	 * Adds a namespace that can be loaded from any of the directories.
	 *
	 * @param array|string $directories
	 * @since Method available since Release
	 */
	public function addNamespace($name, $directories) {
		$this->namespaces[$name] = $this->checkDirectories($directories);
	}

	/**
	 * Adds a prefix that can be loaded from any of the directories.
	 *
	 * @param array|string $directories
	 * @since Method available since Release
	 */
	public function addPrefix($name, $directories) {
		$this->prefixes[$name] = $this->checkDirectories($directories);
	}

	/**
	 * Ensures that the directories are given as an array and checks that each
	 * directory exists.
	 *
	 * @param  array|string $directories
	 * @return array
	 * @since  Method available since Release
	 */
	protected function checkDirectories($directories) {
		if ( ! is_array($directories)) {
			$directories = array($directories);
		}

		foreach ($directories as $directory) {
			if ( ! is_dir($directory)) {
				throw new PHPUnit_Util_Autoloader_Exception("The directory \"$directory\" doesn't exist or isn't readable.");
			}
		}

		return $directories;
	}
}
