<?php declare(strict_types = 1);

namespace PHPStan;

use Composer\Autoload\ClassLoader;
use function class_exists;
use const PHP_VERSION_ID;

final class PharAutoloader
{
	/** @var ClassLoader */
	private static $composerAutoloader;

	/** @var bool */
	private static $polyfillsLoaded = false;

	final public static function loadClass(string $class): void {
		if (!extension_loaded('phar') || defined('__PHPSTAN_RUNNING__')) {
			return;
		}

		if (strpos($class, '_PHPStan_') === 0) {
			if (!in_array('phar', stream_get_wrappers(), true)) {
				throw new \Exception('Phar wrapper is not registered. Please review your php.ini settings.');
			}

			if (self::$composerAutoloader === null) {
				self::$composerAutoloader = require 'phar://' . __DIR__ . '/phpstan.phar/vendor/autoload.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/jetbrains/phpstorm-stubs/PhpStormStubsMap.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/react/async/src/functions_include.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/react/promise/src/functions_include.php';
			}
			self::$composerAutoloader->loadClass($class);

			return;
		}
		if (strpos($class, 'PHPStan\\') !== 0 || strpos($class, 'PHPStan\\PhpDocParser\\') === 0) {
			return;
		}

		if (!in_array('phar', stream_get_wrappers(), true)) {
			throw new \Exception('Phar wrapper is not registered. Please review your php.ini settings.');
		}

		if (!self::$polyfillsLoaded) {
			self::$polyfillsLoaded = true;

			if (
				PHP_VERSION_ID < 80000
				&& empty($GLOBALS['__composer_autoload_files']['a4a119a56e50fbb293281d9a48007e0e'])
				&& !class_exists(\Symfony\Polyfill\Php80\Php80::class, false)
			) {
				$GLOBALS['__composer_autoload_files']['a4a119a56e50fbb293281d9a48007e0e'] = true;
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-php80/Php80.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-php80/bootstrap.php';
			}

			if (
				empty($GLOBALS['__composer_autoload_files']['0e6d7bf4a5811bfa5cf40c5ccd6fae6a'])
				&& !class_exists(\Symfony\Polyfill\Mbstring\Mbstring::class, false)
			) {
				$GLOBALS['__composer_autoload_files']['0e6d7bf4a5811bfa5cf40c5ccd6fae6a'] = true;
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-mbstring/Mbstring.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-mbstring/bootstrap.php';
			}

			if (
				empty($GLOBALS['__composer_autoload_files']['e69f7f6ee287b969198c3c9d6777bd38'])
				&& !class_exists(\Symfony\Polyfill\Intl\Normalizer\Normalizer::class, false)
			) {
				$GLOBALS['__composer_autoload_files']['e69f7f6ee287b969198c3c9d6777bd38'] = true;
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-intl-normalizer/Normalizer.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-intl-normalizer/bootstrap.php';
			}

			if (
				!extension_loaded('intl')
				&& empty($GLOBALS['__composer_autoload_files']['8825ede83f2f289127722d4e842cf7e8'])
				&& !class_exists(\Symfony\Polyfill\Intl\Grapheme\Grapheme::class, false)
			) {
				$GLOBALS['__composer_autoload_files']['8825ede83f2f289127722d4e842cf7e8'] = true;
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-intl-grapheme/Grapheme.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-intl-grapheme/bootstrap.php';
			}

			if (
				PHP_VERSION_ID < 80100
				&& empty ($GLOBALS['__composer_autoload_files']['23c18046f52bef3eea034657bafda50f'])
				&& !class_exists(\Symfony\Polyfill\Php81\Php81::class, false)
			) {
				$GLOBALS['__composer_autoload_files']['23c18046f52bef3eea034657bafda50f'] = true;
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-php81/Php81.php';
				require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-php81/bootstrap.php';
			}

            if (
                PHP_VERSION_ID < 80300
                && empty ($GLOBALS['__composer_autoload_files']['662a729f963d39afe703c9d9b7ab4a8c'])
                && !class_exists(\Symfony\Polyfill\Php83\Php83::class, false)
            ) {
                $GLOBALS['__composer_autoload_files']['662a729f963d39afe703c9d9b7ab4a8c'] = true;
                require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-php83/Php83.php';
                require_once 'phar://' . __DIR__ . '/phpstan.phar/vendor/symfony/polyfill-php83/bootstrap.php';
            }
		}

		$filename = str_replace('\\', DIRECTORY_SEPARATOR, $class);
		if (strpos($class, 'PHPStan\\BetterReflection\\') === 0) {
			$filename = substr($filename, strlen('PHPStan\\BetterReflection\\'));
			$filepath = 'phar://' . __DIR__ . '/phpstan.phar/vendor/ondrejmirtes/better-reflection/src/' . $filename . '.php';
		} else {
			$filename = substr($filename, strlen('PHPStan\\'));
			$filepath = 'phar://' . __DIR__ . '/phpstan.phar/src/' . $filename . '.php';
		}

		if (!file_exists($filepath)) {
			return;
		}

		require $filepath;
	}
}

spl_autoload_register([PharAutoloader::class, 'loadClass']);
