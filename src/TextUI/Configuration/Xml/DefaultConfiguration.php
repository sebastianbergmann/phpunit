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

use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\CodeCoverage;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Filter\DirectoryCollection as CodeCoverageFilterDirectoryCollection;
use PHPUnit\TextUI\XmlConfiguration\Logging\Logging;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class DefaultConfiguration extends Configuration
{
    public static function create(): self
    {
        return new self(
            ExtensionCollection::fromArray([]),
            new CodeCoverage(
                null,
                CodeCoverageFilterDirectoryCollection::fromArray([]),
                FileCollection::fromArray([]),
                CodeCoverageFilterDirectoryCollection::fromArray([]),
                FileCollection::fromArray([]),
                false,
                true,
                false,
                false,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
            ),
            new Groups(
                GroupCollection::fromArray([]),
                GroupCollection::fromArray([])
            ),
            new Groups(
                GroupCollection::fromArray([]),
                GroupCollection::fromArray([])
            ),
            new Logging(
                null,
                null,
                null,
                null,
                null,
                null
            ),
            new Php(
                DirectoryCollection::fromArray([]),
                IniSettingCollection::fromArray([]),
                ConstantCollection::fromArray([]),
                VariableCollection::fromArray([]),
                VariableCollection::fromArray([]),
                VariableCollection::fromArray([]),
                VariableCollection::fromArray([]),
                VariableCollection::fromArray([]),
                VariableCollection::fromArray([]),
                VariableCollection::fromArray([]),
                VariableCollection::fromArray([])
            ),
            new PHPUnit(
                null,
                true,
                null,
                80,
                DefaultResultPrinter::COLOR_DEFAULT,
                false,
                false,
                false,
                false,
                true,
                true,
                true,
                true,
                false,
                null,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                null,
                false,
                false,
                true,
                false,
                false,
                1,
                1,
                10,
                60,
                null,
                TestSuiteSorter::ORDER_DEFAULT,
                true,
                false,
                false,
                false,
                false,
                false
            ),
            TestSuiteCollection::fromArray([])
        );
    }

    public function isDefault(): bool
    {
        return true;
    }
}
