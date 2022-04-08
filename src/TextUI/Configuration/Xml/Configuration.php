<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\CodeCoverage;
use PHPUnit\TextUI\XmlConfiguration\Logging\Logging;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
abstract class Configuration
{
    private ExtensionCollection $extensions;
    private CodeCoverage $codeCoverage;
    private Groups $groups;
    private Groups $testdoxGroups;
    private Logging $logging;
    private Php $php;
    private PHPUnit $phpunit;
    private TestSuiteCollection $testSuite;

    public function __construct(ExtensionCollection $extensions, CodeCoverage $codeCoverage, Groups $groups, Groups $testdoxGroups, Logging $logging, Php $php, PHPUnit $phpunit, TestSuiteCollection $testSuite)
    {
        $this->extensions    = $extensions;
        $this->codeCoverage  = $codeCoverage;
        $this->groups        = $groups;
        $this->testdoxGroups = $testdoxGroups;
        $this->logging       = $logging;
        $this->php           = $php;
        $this->phpunit       = $phpunit;
        $this->testSuite     = $testSuite;
    }

    public function extensions(): ExtensionCollection
    {
        return $this->extensions;
    }

    public function codeCoverage(): CodeCoverage
    {
        return $this->codeCoverage;
    }

    public function groups(): Groups
    {
        return $this->groups;
    }

    public function testdoxGroups(): Groups
    {
        return $this->testdoxGroups;
    }

    public function logging(): Logging
    {
        return $this->logging;
    }

    public function php(): Php
    {
        return $this->php;
    }

    public function phpunit(): PHPUnit
    {
        return $this->phpunit;
    }

    public function testSuite(): TestSuiteCollection
    {
        return $this->testSuite;
    }

    /**
     * @psalm-assert-if-true DefaultConfiguration $this
     */
    public function isDefault(): bool
    {
        return false;
    }

    /**
     * @psalm-assert-if-true LoadedFromFileConfiguration $this
     */
    public function wasLoadedFromFile(): bool
    {
        return false;
    }
}
