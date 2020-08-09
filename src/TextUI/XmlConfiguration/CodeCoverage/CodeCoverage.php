<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage;

use function count;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter\DirectoryCollection;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Clover;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Crap4j;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Html;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Text;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Xml;
use PHPUnit\TextUI\XmlConfiguration\Directory;
use PHPUnit\TextUI\XmlConfiguration\Exception;
use PHPUnit\TextUI\XmlConfiguration\FileCollection;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class CodeCoverage
{
    /**
     * @var ?Directory
     */
    private $cacheDirectory;

    /**
     * @var DirectoryCollection
     */
    private $directories;

    /**
     * @var FileCollection
     */
    private $files;

    /**
     * @var DirectoryCollection
     */
    private $excludeDirectories;

    /**
     * @var FileCollection
     */
    private $excludeFiles;

    /**
     * @var bool
     */
    private $pathCoverage;

    /**
     * @var bool
     */
    private $includeUncoveredFiles;

    /**
     * @var bool
     */
    private $processUncoveredFiles;

    /**
     * @var bool
     */
    private $ignoreDeprecatedCodeUnits;

    /**
     * @var bool
     */
    private $disableCodeCoverageIgnore;

    /**
     * @var ?Clover
     */
    private $clover;

    /**
     * @var ?Crap4j
     */
    private $crap4j;

    /**
     * @var ?Html
     */
    private $html;

    /**
     * @var ?Php
     */
    private $php;

    /**
     * @var ?Text
     */
    private $text;

    /**
     * @var ?Xml
     */
    private $xml;

    public function __construct(?Directory $cacheDirectory, DirectoryCollection $directories, FileCollection $files, DirectoryCollection $excludeDirectories, FileCollection $excludeFiles, bool $pathCoverage, bool $includeUncoveredFiles, bool $processUncoveredFiles, bool $ignoreDeprecatedCodeUnits, bool $disableCodeCoverageIgnore, ?Clover $clover, ?Crap4j $crap4j, ?Html $html, ?Php $php, ?Text $text, ?Xml $xml)
    {
        $this->cacheDirectory            = $cacheDirectory;
        $this->directories               = $directories;
        $this->files                     = $files;
        $this->excludeDirectories        = $excludeDirectories;
        $this->excludeFiles              = $excludeFiles;
        $this->pathCoverage              = $pathCoverage;
        $this->includeUncoveredFiles     = $includeUncoveredFiles;
        $this->processUncoveredFiles     = $processUncoveredFiles;
        $this->ignoreDeprecatedCodeUnits = $ignoreDeprecatedCodeUnits;
        $this->disableCodeCoverageIgnore = $disableCodeCoverageIgnore;
        $this->clover                    = $clover;
        $this->crap4j                    = $crap4j;
        $this->html                      = $html;
        $this->php                       = $php;
        $this->text                      = $text;
        $this->xml                       = $xml;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheDirectory
     */
    public function hasCacheDirectory(): bool
    {
        return $this->cacheDirectory !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheDirectory(): Directory
    {
        if (!$this->hasCacheDirectory()) {
            throw new Exception(
                'No cache directory has been configured'
            );
        }

        return $this->cacheDirectory;
    }

    public function hasNonEmptyListOfFilesToBeIncludedInCodeCoverageReport(): bool
    {
        return count($this->directories) > 0 || count($this->files) > 0;
    }

    public function directories(): DirectoryCollection
    {
        return $this->directories;
    }

    public function files(): FileCollection
    {
        return $this->files;
    }

    public function excludeDirectories(): DirectoryCollection
    {
        return $this->excludeDirectories;
    }

    public function excludeFiles(): FileCollection
    {
        return $this->excludeFiles;
    }

    public function pathCoverage(): bool
    {
        return $this->pathCoverage;
    }

    public function includeUncoveredFiles(): bool
    {
        return $this->includeUncoveredFiles;
    }

    public function ignoreDeprecatedCodeUnits(): bool
    {
        return $this->ignoreDeprecatedCodeUnits;
    }

    public function disableCodeCoverageIgnore(): bool
    {
        return $this->disableCodeCoverageIgnore;
    }

    public function processUncoveredFiles(): bool
    {
        return $this->processUncoveredFiles;
    }

    /**
     * @psalm-assert-if-true !null $this->clover
     */
    public function hasClover(): bool
    {
        return $this->clover !== null;
    }

    /**
     * @throws Exception
     */
    public function clover(): Clover
    {
        if (!$this->hasClover()) {
            throw new Exception(
                'Code Coverage report "Clover XML" has not been configured'
            );
        }

        return $this->clover;
    }

    /**
     * @psalm-assert-if-true !null $this->crap4j
     */
    public function hasCrap4j(): bool
    {
        return $this->crap4j !== null;
    }

    /**
     * @throws Exception
     */
    public function crap4j(): Crap4j
    {
        if (!$this->hasCrap4j()) {
            throw new Exception(
                'Code Coverage report "Crap4J" has not been configured'
            );
        }

        return $this->crap4j;
    }

    /**
     * @psalm-assert-if-true !null $this->html
     */
    public function hasHtml(): bool
    {
        return $this->html !== null;
    }

    /**
     * @throws Exception
     */
    public function html(): Html
    {
        if (!$this->hasHtml()) {
            throw new Exception(
                'Code Coverage report "HTML" has not been configured'
            );
        }

        return $this->html;
    }

    /**
     * @psalm-assert-if-true !null $this->php
     */
    public function hasPhp(): bool
    {
        return $this->php !== null;
    }

    /**
     * @throws Exception
     */
    public function php(): Php
    {
        if (!$this->hasPhp()) {
            throw new Exception(
                'Code Coverage report "PHP" has not been configured'
            );
        }

        return $this->php;
    }

    /**
     * @psalm-assert-if-true !null $this->text
     */
    public function hasText(): bool
    {
        return $this->text !== null;
    }

    /**
     * @throws Exception
     */
    public function text(): Text
    {
        if (!$this->hasText()) {
            throw new Exception(
                'Code Coverage report "Text" has not been configured'
            );
        }

        return $this->text;
    }

    /**
     * @psalm-assert-if-true !null $this->xml
     */
    public function hasXml(): bool
    {
        return $this->xml !== null;
    }

    /**
     * @throws Exception
     */
    public function xml(): Xml
    {
        if (!$this->hasXml()) {
            throw new Exception(
                'Code Coverage report "XML" has not been configured'
            );
        }

        return $this->xml;
    }
}
