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

use function array_intersect;
use function array_unique;
use function sprintf;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\TextUI\Configuration\Registry;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ListTestFilesCommand implements Command
{
    private readonly TestSuite $suite;

    public function __construct(TestSuite $suite)
    {
        $this->suite = $suite;
    }

    /**
     * @throws ReflectionException
     */
    public function execute(): Result
    {
        $configuration = Registry::get();

        $buffer = 'Available test files:' . PHP_EOL;

        $results = [];

        foreach (new RecursiveIteratorIterator($this->suite) as $test) {
            if ($test instanceof TestCase) {
                $name = (new ReflectionClass($test))->getFileName();

                // @codeCoverageIgnoreStart
                if ($name === false) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                if ($configuration->hasGroups() && empty(array_intersect($configuration->groups(), $test->groups()))) {
                    continue;
                }

                if ($configuration->hasExcludeGroups() && !empty(array_intersect($configuration->excludeGroups(), $test->groups()))) {
                    continue;
                }
            } elseif ($test instanceof PhptTestCase) {
                $name = $test->getName();
            } else {
                continue;
            }

            $results[] = $name;
        }

        foreach (array_unique($results) as $result) {
            $buffer .= sprintf(
                ' - %s' . PHP_EOL,
                $result,
            );
        }

        return Result::from($buffer);
    }
}
