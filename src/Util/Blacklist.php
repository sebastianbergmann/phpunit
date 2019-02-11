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
use PharIo\Manifest\Manifest;
use PharIo\Version\Version as PharIoVersion;
use PHP_Token;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Project;
use phpDocumentor\Reflection\Type;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeUnitReverseLookup\Wizard;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Environment\Runtime;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use SebastianBergmann\GlobalState\Snapshot;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\ObjectEnumerator\Enumerator;
use SebastianBergmann\RecursionContext\Context;
use SebastianBergmann\ResourceOperations\ResourceOperations;
use SebastianBergmann\Timer\Timer;
use SebastianBergmann\Version;
use Text_Template;
use TheSeer\Tokenizer\Tokenizer;
use Webmozart\Assert\Assert;

/**
 * Utility class for blacklisting PHPUnit's own source code files.
 */
final class Blacklist
{
    /**
     * @var array
     */
    public static $blacklistedClassNames = [
        // composer
        ClassLoader::class => 1,

        // doctrine/instantiator
        Instantiator::class => 1,

        // myclabs/deepcopy
        DeepCopy::class => 1,

        // phar-io/manifest
        Manifest::class => 1,

        // phar-io/version
        PharIoVersion::class => 1,

        // phpdocumentor/reflection-common
        Project::class => 1,

        // phpdocumentor/reflection-docblock
        DocBlock::class => 1,

        // phpdocumentor/type-resolver
        Type::class => 1,

        // phpspec/prophecy
        Prophet::class => 1,

        // phpunit/phpunit
        TestCase::class => 2,

        // phpunit/php-code-coverage
        CodeCoverage::class => 1,

        // phpunit/php-file-iterator
        FileIteratorFacade::class => 1,

        // phpunit/php-invoker
        Invoker::class => 1,

        // phpunit/php-text-template
        Text_Template::class => 1,

        // phpunit/php-timer
        Timer::class => 1,

        // phpunit/php-token-stream
        PHP_Token::class => 1,

        // sebastian/code-unit-reverse-lookup
        Wizard::class => 1,

        // sebastian/comparator
        Comparator::class => 1,

        // sebastian/diff
        Diff::class => 1,

        // sebastian/environment
        Runtime::class => 1,

        // sebastian/exporter
        Exporter::class => 1,

        // sebastian/global-state
        Snapshot::class => 1,

        // sebastian/object-enumerator
        Enumerator::class => 1,

        // sebastian/recursion-context
        Context::class => 1,

        // sebastian/resource-operations
        ResourceOperations::class => 1,

        // sebastian/version
        Version::class => 1,

        // theseer/tokenizer
        Tokenizer::class => 1,

        // webmozart/assert
        Assert::class => 1,
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
