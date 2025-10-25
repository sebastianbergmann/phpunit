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

use Webmozart\Glob\Glob;
use function array_map;
use PHPUnit\Util\FileMatcherRegex;

/**
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
            return [$directory, self::toGlob($directory)];
        }, $source->includeDirectories()->asArray());
        $this->excludeDirectoryRegexes = array_map(static function (FilterDirectory $directory)
        {
            return [$directory, self::toGlob($directory)];
        }, $source->excludeDirectories()->asArray());
    }

    /**
     * @see https://docs.phpunit.de/en/12.4/configuration.html#the-include-element
     */
    public function includes(string $path): bool
    {
        $included = false;
        $dirPath = dirname($path) . '/';
        $filename = basename($path);
        foreach ($this->source->includeFiles() as $file) {
            if ($file->path() === $path) {
                $included = true;
            }
        }

        foreach ($this->includeDirectoryRegexes as [$directory, $directoryRegex]) {
            if (preg_match($directoryRegex, $dirPath) && self::filenameMatches($directory, $filename)) {
                $included = true;
            }
        }

        foreach ($this->source->excludeFiles() as $file) {
            if ($file->path() === $path) {
                $included = false;
            }
        }

        foreach ($this->excludeDirectoryRegexes as [$directory, $directoryRegex]) {
            if (preg_match($directoryRegex, $dirPath) && self::filenameMatches($directory, $filename)) {
                $included = false;
            }
        }

        return $included;
    }

    public static function toGlob(FilterDirectory $directory): string
    {
        $path = $directory->path();

        if (Glob::isDynamic($path)) {
            return Glob::toRegEx($path);
        }

        return Glob::toRegEx(sprintf('%s/**/*', $directory->path()));
    }

    private static function filenameMatches(FilterDirectory $directory, string $filename): bool
    {
        return str_starts_with($filename, $directory->prefix()) && str_ends_with($filename, $directory->suffix());
    }
}
