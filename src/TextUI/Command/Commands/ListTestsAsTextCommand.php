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
use RecursiveIteratorIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ListTestsAsTextCommand implements Command
{
    private TestSuite $suite;

    public function __construct(TestSuite $suite)
    {
        $this->suite = $suite;
    }

    public function execute(): Result
    {
        $buffer = 'Available test(s):' . PHP_EOL;

        foreach (new RecursiveIteratorIterator($this->suite->getIterator()) as $test) {
            if ($test instanceof TestCase) {
                $name = sprintf(
                    '%s::%s',
                    $test::class,
                    str_replace(' with data set ', '', $test->getName())
                );
            } elseif ($test instanceof PhptTestCase) {
                $name = $test->getName();
            } else {
                continue;
            }

            $buffer .= sprintf(
                ' - %s' . PHP_EOL,
                $name
            );
        }

        return Result::from($buffer);
    }
}
