<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use function array_map;
use PHPUnit\Util\FileMatcher;
use PHPUnit\Util\FileMatcherRegex;

/**
 * TODO: Does not take into account suffixes and prefixes - and tests don't cover it.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SourceFilter
{
    private static ?self $instance = null;
    private Source $source;

    /**
     * @var list<FileMatcherRegex>
     */
    private array $includeDirectoryRegexes;

    /**
     * @var list<FileMatcherRegex>
     */
    private array $excludeDirectoryRegexes;

    public static function instance(): self
    {
        if (self::$instance === null) {
            $source         = Registry::get()->source();
            self::$instance = new self($source);

            return self::$instance;
        }

        return self::$instance;
    }

    public function __construct(Source $source)
    {
        $this->source                  = $source;
        $this->includeDirectoryRegexes = array_map(static function (FilterDirectory $directory)
        {
            return FileMatcher::toRegEx($directory->path());
        }, $source->includeDirectories()->asArray());
        $this->excludeDirectoryRegexes = array_map(static function (FilterDirectory $directory)
        {
            return FileMatcher::toRegEx($directory->path());
        }, $source->excludeDirectories()->asArray());
    }

    public function includes(string $path): bool
    {
        $included = false;

        foreach ($this->source->includeFiles() as $file) {
            if ($file->path() === $path) {
                $included = true;
            }
        }

        foreach ($this->includeDirectoryRegexes as $directoryRegex) {
            if ($directoryRegex->matches($path)) {
                $included = true;
            }
        }

        foreach ($this->source->excludeFiles() as $file) {
            if ($file->path() === $path) {
                $included = false;
            }
        }

        foreach ($this->excludeDirectoryRegexes as $directoryRegex) {
            if ($directoryRegex->matches($path)) {
                $included = false;
            }
        }

        return $included;
    }
}
