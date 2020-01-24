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

use PHPUnit\TextUI\Configuration\Logging\Logging;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Configuration
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var array
     * @psalm-var array<int,array<int,string>>
     */
    private $validationErrors = [];

    /**
     * @var ExtensionCollection
     */
    private $extensions;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var Groups
     */
    private $groups;

    /**
     * @var Groups
     */
    private $testdoxGroups;

    /**
     * @var ExtensionCollection
     */
    private $listeners;

    /**
     * @var Logging
     */
    private $logging;

    /**
     * @var Php
     */
    private $php;

    /**
     * @var PHPUnit
     */
    private $phpunit;

    /**
     * @var TestSuiteCollection
     */
    private $testSuite;

    /**
     * @psalm-param array<int,array<int,string>> $validationErrors
     */
    public function __construct(string $filename, array $validationErrors, ExtensionCollection $extensions, Filter $filter, Groups $groups, Groups $testdoxGroups, ExtensionCollection $listeners, Logging $logging, Php $php, PHPUnit $phpunit, TestSuiteCollection $testSuite)
    {
        $this->filename         = $filename;
        $this->validationErrors = $validationErrors;
        $this->extensions       = $extensions;
        $this->filter           = $filter;
        $this->groups           = $groups;
        $this->testdoxGroups    = $testdoxGroups;
        $this->listeners        = $listeners;
        $this->logging          = $logging;
        $this->php              = $php;
        $this->phpunit          = $phpunit;
        $this->testSuite        = $testSuite;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function hasValidationErrors(): bool
    {
        return \count($this->validationErrors) > 0;
    }

    public function validationErrors(): array
    {
        return $this->validationErrors;
    }

    public function extensions(): ExtensionCollection
    {
        return $this->extensions;
    }

    public function filter(): Filter
    {
        return $this->filter;
    }

    public function groups(): Groups
    {
        return $this->groups;
    }

    public function testdoxGroups(): Groups
    {
        return $this->testdoxGroups;
    }

    public function listeners(): ExtensionCollection
    {
        return $this->listeners;
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
}
