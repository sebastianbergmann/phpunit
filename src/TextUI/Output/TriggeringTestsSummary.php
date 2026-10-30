<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output;

use function array_key_first;
use function array_slice;
use function array_values;
use function assert;
use function count;
use function implode;
use function sprintf;
use function usort;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\TestRunner\TestResult\Issues\Issue;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TriggeringTestsSummary
{
    /**
     * @var non-empty-string
     */
    private string $description;

    /**
     * @var non-empty-list<non-empty-string>
     */
    private array $entries;

    /**
     * @param positive-int $limit
     */
    public static function from(Issue $issue, int $limit): self
    {
        $methods = [];

        foreach ($issue->triggeringTests() as $id => $triggeringTest) {
            $test    = $triggeringTest['test'];
            $class   = $id;
            $method  = $id;
            $dataSet = null;

            if ($test->isTestMethod()) {
                assert($test instanceof TestMethod);

                $class  = $test->className();
                $method = $class . '::' . $test->methodName();

                if ($test->testData()->hasDataFromDataProvider()) {
                    $dataSet = $test->testData()->dataFromDataProvider()->dataSetName();
                }
            }

            if (!isset($methods[$method])) {
                $methods[$method] = ['class' => $class, 'name' => $method, 'id' => $id, 'tests' => 0, 'dataSets' => [], 'count' => 0];
            }

            $methods[$method]['tests']++;
            $methods[$method]['count'] += $triggeringTest['count'];

            if ($dataSet !== null) {
                $methods[$method]['dataSets'][$dataSet] = true;
            }
        }

        $classes = [];

        foreach ($methods as $method) {
            $classes[$method['class']][] = $method;
        }

        $groupedByTestClass = count($methods) > $limit && count($classes) > 1 && count($classes) < count($methods);
        $entries            = [];

        if ($groupedByTestClass) {
            foreach ($classes as $class => $methodsOfClass) {
                if (count($methodsOfClass) === 1) {
                    $entries[] = self::methodEntry($methodsOfClass[0]);

                    continue;
                }

                $tests = 0;
                $count = 0;

                foreach ($methodsOfClass as $method) {
                    $tests += $method['tests'];
                    $count += $method['count'];
                }

                $entries[] = [
                    'label' => $class . self::details([self::plural($tests, 'test', 'tests')], $tests, $count),
                    'tests' => $tests,
                    'count' => $count,
                ];
            }
        } else {
            foreach ($methods as $method) {
                $entries[] = self::methodEntry($method);
            }
        }

        usort(
            $entries,
            static fn (array $a, array $b): int => [$b['count'], $b['tests'], $a['label']] <=> [$a['count'], $a['tests'], $b['label']],
        );

        $labels = [];

        foreach (array_slice($entries, 0, $limit) as $entry) {
            $labels[] = $entry['label'];
        }

        $omittedEntries = array_slice($entries, $limit);

        if ($omittedEntries !== [] && $groupedByTestClass) {
            $labels[] = '... ' . self::plural(count($omittedEntries), 'more test class', 'more test classes');
        } elseif ($omittedEntries !== []) {
            $numberOfOmittedTests = 0;

            foreach ($omittedEntries as $entry) {
                $numberOfOmittedTests += $entry['tests'];
            }

            $labels[] = '... ' . self::plural($numberOfOmittedTests, 'more test', 'more tests');
        }

        $description = sprintf(
            'Triggered %s%s by %s',
            self::plural($issue->numberOfTimesTriggered(), 'time', 'times'),
            self::triggerTypes($issue),
            self::plural(count($issue->triggeringTests()), 'test', 'tests'),
        );

        if ($groupedByTestClass) {
            $description .= ' in ' . self::plural(count($classes), 'test class', 'test classes');
        }

        return new self($description, $labels);
    }

    /**
     * @param non-empty-string                 $description
     * @param non-empty-list<non-empty-string> $entries
     */
    private function __construct(string $description, array $entries)
    {
        $this->description = $description;
        $this->entries     = $entries;
    }

    /**
     * @return non-empty-string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function entries(): array
    {
        return $this->entries;
    }

    /**
     * @param array{class: non-empty-string, name: non-empty-string, id: non-empty-string, tests: positive-int, dataSets: array<int|string, true>, count: int} $method
     *
     * @return array{label: non-empty-string, tests: positive-int, count: int}
     */
    private static function methodEntry(array $method): array
    {
        if ($method['tests'] === 1) {
            return [
                'label' => $method['id'] . self::details([], 1, $method['count']),
                'tests' => 1,
                'count' => $method['count'],
            ];
        }

        $details = [];

        if (count($method['dataSets']) > 1) {
            $details[] = self::plural(count($method['dataSets']), 'data set', 'data sets');
        }

        if ($method['tests'] > count($method['dataSets'])) {
            $details[] = self::plural($method['tests'], 'test', 'tests');
        }

        return [
            'label' => $method['name'] . self::details($details, $method['tests'], $method['count']),
            'tests' => $method['tests'],
            'count' => $method['count'],
        ];
    }

    /**
     * @param list<non-empty-string> $details
     */
    private static function details(array $details, int $numberOfTests, int $numberOfTimesTriggered): string
    {
        if ($numberOfTimesTriggered > $numberOfTests) {
            $details[] = self::plural($numberOfTimesTriggered, 'time', 'times');
        }

        if ($details === []) {
            return '';
        }

        return ' (' . implode(', ', $details) . ')';
    }

    private static function triggerTypes(Issue $issue): string
    {
        $triggerTypes = $issue->triggerTypes();

        if ($triggerTypes === []) {
            return '';
        }

        if (count($triggerTypes) === 1 && array_values($triggerTypes)[0] === $issue->numberOfTimesTriggered()) {
            return ' (' . array_key_first($triggerTypes) . ')';
        }

        $buffer = [];

        foreach ($triggerTypes as $triggerType => $count) {
            $buffer[] = $count . ' ' . $triggerType;
        }

        return ' (' . implode(', ', $buffer) . ')';
    }

    /**
     * @return non-empty-string
     */
    private static function plural(int $number, string $singular, string $plural): string
    {
        return $number . ' ' . ($number === 1 ? $singular : $plural);
    }
}
