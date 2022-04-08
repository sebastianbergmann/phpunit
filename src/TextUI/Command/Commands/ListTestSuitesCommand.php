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
use PHPUnit\TextUI\XmlConfiguration\TestSuiteCollection;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ListTestSuitesCommand implements Command
{
    private TestSuiteCollection $suites;

    public function __construct(TestSuiteCollection $suites)
    {
        $this->suites = $suites;
    }

    public function execute(): Result
    {
        $buffer = 'Available test suite(s):' . PHP_EOL;

        foreach ($this->suites as $suite) {
            $buffer .= sprintf(
                ' - %s' . PHP_EOL,
                $suite->name()
            );
        }

        return Result::from($buffer);
    }
}
