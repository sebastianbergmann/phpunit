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
use PHPUnit\Util\Xml\ValidationResult;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class LoadedFromFileConfiguration extends Configuration
{
    private string $filename;
    private ValidationResult $validationResult;

    public function __construct(string $filename, ValidationResult $validationResult, ExtensionCollection $extensions, CodeCoverage $codeCoverage, Groups $groups, Groups $testdoxGroups, Logging $logging, Php $php, PHPUnit $phpunit, TestSuiteCollection $testSuite)
    {
        $this->filename         = $filename;
        $this->validationResult = $validationResult;

        parent::__construct(
            $extensions,
            $codeCoverage,
            $groups,
            $testdoxGroups,
            $logging,
            $php,
            $phpunit,
            $testSuite
        );
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function hasValidationErrors(): bool
    {
        return $this->validationResult->hasValidationErrors();
    }

    public function validationErrors(): string
    {
        return $this->validationResult->asString();
    }

    public function wasLoadedFromFile(): bool
    {
        return true;
    }
}
