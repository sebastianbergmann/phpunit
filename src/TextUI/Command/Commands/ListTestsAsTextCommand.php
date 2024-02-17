<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use function sprintf;
use function str_replace;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\TextUI\Configuration\Registry;
use RecursiveIteratorIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListTestsAsTextCommand implements Command
{
    private TestSuite $suite;

    public function __construct(TestSuite $suite)
    {
        $this->suite = $suite;
    }

    public function execute(): Result
    {
        $buffer = $this->warnAboutConflictingOptions();

        $buffer .= 'Available test(s):' . PHP_EOL;

        $configuration = Registry::get();
        $configuredGroups = [];
        if ($configuration->hasGroups()) {
            $configuredGroups = $configuration->groups();
        }

        foreach (new RecursiveIteratorIterator($this->suite) as $test) {
            if ($test instanceof TestCase) {
                if (!$this->testContainsConfiguredGroups($test, $configuredGroups)) {
                    continue;
                }

                $name = sprintf(
                    '%s::%s',
                    $test::class,
                    str_replace(' with data set ', '', $test->nameWithDataSet()),
                );
            } elseif ($test instanceof PhptTestCase) {
                if ($configuredGroups !== []) {
                    continue;
                }

                $name = $test->getName();
            } else {
                continue;
            }

            $buffer .= sprintf(
                ' - %s' . PHP_EOL,
                $name,
            );
        }

        return Result::from($buffer);
    }

    private function warnAboutConflictingOptions(): string
    {
        $buffer = '';

        $configuration = Registry::get();

        if ($configuration->hasFilter()) {
            $buffer .= 'The --filter and --list-tests options cannot be combined, --filter is ignored' . PHP_EOL;
        }

        if ($configuration->hasExcludeGroups()) {
            $buffer .= 'The --exclude-group and --list-tests options cannot be combined, --exclude-group is ignored' . PHP_EOL;
        }

        if (!empty($buffer)) {
            $buffer .= PHP_EOL;
        }

        return $buffer;
    }

    private function testContainsConfiguredGroups(TestCase $test, array $configuredGroups): bool
    {
        if ($configuredGroups === []) {
            return true;
        }

        $testGroups = $test->groups();

        if ($testGroups === []) {
            return false;
        }

        foreach ($configuredGroups as $group) {
            if (!in_array($group, $testGroups, true)) {
                return false;
            }
        }

        return true;
    }
}
