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

use const PHP_EOL;
use function count;
use function ksort;
use function sprintf;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\Configuration\Registry;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListTestSuitesCommand implements Command
{
    private TestSuite $testSuite;

    public function __construct(TestSuite $testSuite)
    {
        $this->testSuite = $testSuite;
    }

    public function execute(): Result
    {
        /** @var array<non-empty-string, positive-int> $suites */
        $suites = [];

        foreach ($this->testSuite->tests() as $test) {
            if (!$test instanceof TestSuite) {
                continue;
            }

            $suites[$test->name()] = count($test->collect());
        }

        ksort($suites);

        $buffer = $this->warnAboutConflictingOptions();

        $buffer .= sprintf(
            'Available test suite%s:' . PHP_EOL,
            count($suites) > 1 ? 's' : '',
        );

        foreach ($suites as $suite => $numberOfTests) {
            $buffer .= sprintf(
                ' - %s (%d test%s)' . PHP_EOL,
                $suite,
                $numberOfTests,
                $numberOfTests > 1 ? 's' : '',
            );
        }

        return Result::from($buffer);
    }

    private function warnAboutConflictingOptions(): string
    {
        $buffer = '';

        $configuration = Registry::get();

        if ($configuration->hasFilter()) {
            $buffer .= 'The --filter and --list-suites options cannot be combined, --filter is ignored' . PHP_EOL;
        }

        if ($configuration->hasGroups()) {
            $buffer .= 'The --group and --list-suites options cannot be combined, --group is ignored' . PHP_EOL;
        }

        if ($configuration->hasExcludeGroups()) {
            $buffer .= 'The --exclude-group and --list-suites options cannot be combined, --exclude-group is ignored' . PHP_EOL;
        }

        if ($configuration->includeTestSuite() !== '') {
            $buffer .= 'The --testsuite and --list-suites options cannot be combined, --exclude-group is ignored' . PHP_EOL;
        }

        if (!empty($buffer)) {
            $buffer .= PHP_EOL;
        }

        return $buffer;
    }
}
