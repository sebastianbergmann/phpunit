<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use Composer\Autoload\ClassLoader;
use DeepCopy\DeepCopy;
use Doctrine\Instantiator\Instantiator;
use PHP_Token;
use phpDocumentor\Reflection\DocBlock;
use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Environment\Runtime;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use SebastianBergmann\GlobalState\Snapshot;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\RecursionContext\Context;
use SebastianBergmann\Timer\Timer;
use SebastianBergmann\Version;
use Text_Template;

/**
 * Utility class for blacklisting PHPUnit's own source code files.
 */
final class Blacklist
{
    /**
     * @var array
     */
    public static $blacklistedClassNames = [
        FileIteratorFacade::class     => 1,
        Timer::class                  => 1,
        PHP_Token::class              => 1,
        TestCase::class               => 2,
        'PHPUnit\DbUnit\TestCase'     => 2,
        Generator::class              => 1,
        Text_Template::class          => 1,
        'Symfony\Component\Yaml\Yaml' => 1,
        CodeCoverage::class           => 1,
        Diff::class                   => 1,
        Runtime::class                => 1,
        Comparator::class             => 1,
        Exporter::class               => 1,
        Snapshot::class               => 1,
        Invoker::class                => 1,
        Context::class                => 1,
        Version::class                => 1,
        ClassLoader::class            => 1,
        Instantiator::class           => 1,
        DocBlock::class               => 1,
        Prophet::class                => 1,
        DeepCopy::class               => 1
    ];

    /**
     * @var string[]
     */
    private static $directories;

    /**
     * @return string[]
     */
    public function getBlacklistedDirectories(): array
    {
        $this->initialize();

        return self::$directories;
    }

    public function isBlacklisted(string $file): bool
    {
        if (\defined('PHPUNIT_TESTSUITE')) {
            return false;
        }

        $this->initialize();

        foreach (self::$directories as $directory) {
            if (\strpos($file, $directory) === 0) {
                return true;
            }
        }

        return false;
    }

    private function initialize(): void
    {
        if (self::$directories === null) {
            self::$directories = [];

            foreach (self::$blacklistedClassNames as $className => $parent) {
                if (!\class_exists($className)) {
                    continue;
                }

                $reflector = new ReflectionClass($className);
                $directory = $reflector->getFileName();

                for ($i = 0; $i < $parent; $i++) {
                    $directory = \dirname($directory);
                }

                self::$directories[] = $directory;
            }

            // Hide process isolation workaround on Windows.
            if (\DIRECTORY_SEPARATOR === '\\') {
                // tempnam() prefix is limited to first 3 chars.
                // @see https://php.net/manual/en/function.tempnam.php
                self::$directories[] = \sys_get_temp_dir() . '\\PHP';
            }
        }
    }
}
