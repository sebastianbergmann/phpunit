<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function assert;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Metadata\Group;
use PHPUnit\TestRunner\TestResult\TestResult;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Renderer
{
    /**
     * @psalm-param list<string> $includeGroups
     * @psalm-param list<string> $excludeGroups
     */
    public function render(TestResult $result, array $includeGroups, array $excludeGroups): string
    {
        return $this->doRender(
            $this->testMethodsFilteredByTestDoxGroupsAndGroupedByClassAndSortedByLine($result, $includeGroups, $excludeGroups)
        );
    }

    /**
     * @psalm-param array<class-string,list<TestMethod>> $tests
     */
    abstract protected function doRender(array $tests): string;

    /**
     * @psalm-param list<string> $includeGroups
     * @psalm-param list<string> $excludeGroups
     *
     * @psalm-return array<class-string,list<TestMethod>>
     */
    private function testMethodsFilteredByTestDoxGroupsAndGroupedByClassAndSortedByLine(TestResult $result, array $includeGroups, array $excludeGroups): array
    {
        $tests = [];

        foreach ($result->testMethodsGroupedByClassAndSortedByLine() as $className => $methods) {
            foreach ($methods as $method) {
                if (!$this->isOfInterest($method, $includeGroups, $excludeGroups)) {
                    continue;
                }

                if (!isset($tests[$className])) {
                    $tests[$className] = [];
                }

                $tests[$className][] = $method;
            }
        }

        return $tests;
    }

    private function isOfInterest(TestMethod $test, array $includeGroups, array $excludeGroups): bool
    {
        $groups = $this->groups($test);

        if (!empty($includeGroups)) {
            foreach ($groups as $group) {
                if (in_array($group, $includeGroups, true)) {
                    return true;
                }
            }

            return false;
        }

        if (!empty($excludeGroups)) {
            foreach ($groups as $group) {
                if (in_array($group, $excludeGroups, true)) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }

    /**
     * @psalm-return list<string>
     */
    private function groups(TestMethod $test): array
    {
        $groups = [];

        foreach ($test->metadata()->isGroup() as $group) {
            assert($group instanceof Group);

            $groups[] = $group->groupName();
        }

        return $groups;
    }
}
