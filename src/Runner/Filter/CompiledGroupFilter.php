<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Filter;

use function explode;
use function in_array;
use function str_contains;

/**
 * The values of the --group and --exclude-group CLI options, compiled to the
 * group memberships a test must have to be selected by them.
 *
 * This is the only place where the syntax of those options is parsed. The group
 * filter iterators and the group pruner of the test index both evaluate the
 * result of that parse so that they cannot disagree about which tests a
 * selection selects: a test file that is skipped because the pruner considers
 * no test in it selected is never loaded, and a filter that would have selected
 * a test in it would therefore never see it.
 *
 * "a+b" selects the tests that are in group "a" and in group "b". Every value
 * of the option is such a conjunction of group names, and a test is selected
 * when it matches at least one of them.
 *
 * The "+" is only an operator between two group names: a value that is "+", or
 * that begins or ends with one, is the name of a group. That keeps the syntax
 * from claiming names it does not need, but it cannot keep it from claiming any
 * name at all, because every string is a legal group name. A group whose name
 * is parsed as a conjunction cannot be selected by name, so PHPUnit warns about
 * a name like that where it is declared, see AttributeParser and
 * TestSuiteMapper.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CompiledGroupFilter
{
    /**
     * @var list<non-empty-list<non-empty-string>>
     */
    private array $conjunctions;

    /**
     * The names of the groups that appear in any of the conjunctions, used as
     * keys so that looking one up does not become more expensive as more groups
     * are selected.
     *
     * @var array<non-empty-string, true>
     */
    private array $groupNames;

    /**
     * @param list<non-empty-string> $groups
     */
    public static function from(array $groups): self
    {
        $conjunctions = [];
        $groupNames   = [];

        foreach ($groups as $group) {
            $conjunction = self::parse($group);

            $conjunctions[] = $conjunction;

            foreach ($conjunction as $groupName) {
                $groupNames[$groupName] = true;
            }
        }

        return new self($conjunctions, $groupNames);
    }

    /**
     * Whether the name of a group is parsed as a conjunction of the names of
     * other groups instead of as the name of that group.
     *
     * @param non-empty-string $group
     */
    public static function isConjunction(string $group): bool
    {
        return self::parse($group) !== [$group];
    }

    /**
     * @param list<non-empty-list<non-empty-string>> $conjunctions
     * @param array<non-empty-string, true>          $groupNames
     */
    private function __construct(array $conjunctions, array $groupNames)
    {
        $this->conjunctions = $conjunctions;
        $this->groupNames   = $groupNames;
    }

    public function isEmpty(): bool
    {
        return $this->conjunctions === [];
    }

    /**
     * Whether a group is named by this selection, and knowing which tests are
     * in it is therefore worth the effort.
     *
     * A test that is in the group is not necessarily selected: the group can be
     * one of several in a conjunction.
     *
     * @param non-empty-string $group
     */
    public function mentions(string $group): bool
    {
        return isset($this->groupNames[$group]);
    }

    /**
     * @param list<non-empty-string> $groups
     */
    public function matches(array $groups): bool
    {
        foreach ($this->conjunctions as $conjunction) {
            if ($this->matchesConjunction($conjunction, $groups)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param non-empty-list<non-empty-string> $conjunction
     * @param list<non-empty-string>           $groups
     */
    private function matchesConjunction(array $conjunction, array $groups): bool
    {
        foreach ($conjunction as $group) {
            if (!in_array($group, $groups, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param non-empty-string $group
     *
     * @return non-empty-list<non-empty-string>
     */
    private static function parse(string $group): array
    {
        if (!str_contains($group, '+')) {
            return [$group];
        }

        $conjunction = [];

        foreach (explode('+', $group) as $groupName) {
            if ($groupName === '') {
                return [$group];
            }

            if (in_array($groupName, $conjunction, true)) {
                continue;
            }

            $conjunction[] = $groupName;
        }

        return $conjunction;
    }
}
