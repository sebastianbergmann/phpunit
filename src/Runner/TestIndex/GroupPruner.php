<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use function array_intersect;
use function array_merge;
use function str_starts_with;

/**
 * Decides whether a test file can be skipped for a run that selects tests by
 * group.
 *
 * Unlike a filter for the name of a test, a selection by group can be decided
 * exactly: the groups of a test are a property of its class and its method, and
 * every row of a data provider inherits the groups of its test method, see
 * TestBuilder::buildDataProviderTestSuite(). Knowing the test methods of a file
 * and their groups is therefore enough to know that none of the tests in it can
 * be selected.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class GroupPruner
{
    /**
     * @var list<non-empty-string>
     */
    private array $includedGroups;

    /**
     * @var list<non-empty-string>
     */
    private array $excludedGroups;

    /**
     * The included groups are expected to already contain the virtual groups
     * that back --covers, --uses and --requires-php-extension.
     *
     * @param list<non-empty-string> $includedGroups
     * @param list<non-empty-string> $excludedGroups
     */
    public function __construct(array $includedGroups, array $excludedGroups)
    {
        $this->includedGroups = $includedGroups;
        $this->excludedGroups = $excludedGroups;
    }

    /**
     * Nothing can be skipped when no groups are selected at all.
     */
    public function prunes(): bool
    {
        return $this->includedGroups !== [] || $this->excludedGroups !== [];
    }

    /**
     * The groups configured for a test file in the XML configuration file are
     * added to the groups of every test in it, see TestSuiteMapper::map().
     *
     * @param list<non-empty-string> $groupsFromConfiguration
     */
    public function canSkip(TestIndexEntry $entry, array $groupsFromConfiguration = []): bool
    {
        if (!$this->prunes()) {
            return false;
        }

        /*
         * A test class without test methods makes PHPUnit warn about it. That
         * warning would be lost if the file were skipped, so it is not.
         */
        if ($entry->groups() === []) {
            return false;
        }

        foreach ($entry->groups() as $groups) {
            if ($this->selects($this->effectiveGroups($groups, $groupsFromConfiguration))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mirrors the composition of the group filters in TestSuiteFilterProcessor:
     * a test is run when it is in one of the included groups, if any are
     * selected, and in none of the excluded groups.
     *
     * @param list<non-empty-string> $groups
     */
    private function selects(array $groups): bool
    {
        if ($this->includedGroups !== [] && array_intersect($groups, $this->includedGroups) === []) {
            return false;
        }

        return array_intersect($groups, $this->excludedGroups) === [];
    }

    /**
     * Mirrors TestSuite::addTest(): a test that is in no group other than the
     * virtual ones is in the 'default' group.
     *
     * @param list<non-empty-string> $groups
     * @param list<non-empty-string> $groupsFromConfiguration
     *
     * @return list<non-empty-string>
     */
    private function effectiveGroups(array $groups, array $groupsFromConfiguration): array
    {
        $groups = array_merge($groupsFromConfiguration, $groups);

        foreach ($groups as $group) {
            if (!str_starts_with($group, '__phpunit_')) {
                return $groups;
            }
        }

        $groups[] = 'default';

        return $groups;
    }
}
