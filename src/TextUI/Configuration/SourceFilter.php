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

use PHPUnit\Util\FileMatcher;
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
            $source = Registry::get()->source();
            return new self($source);
        }

        return self::$instance;
    }

    /**
     * @param array<non-empty-string, true> $map
     */
    public function __construct(Source $source)
    {
        $this->source = $source;
        $this->includeDirectoryRegexes = array_map(function (FilterDirectory $directory) {
            return FileMatcher::toRegEx($directory->path());
        }, $source->includeDirectories()->asArray());
        $this->excludeDirectoryRegexes = array_map(function (FilterDirectory $directory) {
            return FileMatcher::toRegEx($directory->path());
        }, $source->excludeDirectories()->asArray());
    }

    public function includes(string $path): bool
    {
        foreach ($this->source->includeDirectories() as $directory) {
        }
        return isset($this->map[$path]);
    }
}
