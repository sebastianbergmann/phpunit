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

use PHPUnit\Runner\Exception;
use PHPUnit\Runner\TestSuiteLoader;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class DefaultTestFileSkipper implements TestFileSkipper
{
    private TestIndex $index;
    private GroupPruner $groupPruner;
    private NameFilterPruner $nameFilterPruner;

    public function __construct(TestIndex $index, GroupPruner $groupPruner, NameFilterPruner $nameFilterPruner)
    {
        $this->index            = $index;
        $this->groupPruner      = $groupPruner;
        $this->nameFilterPruner = $nameFilterPruner;
    }

    /**
     * A file is only skipped when it is indexed, the entry for it is still
     * valid, and none of the tests in it can be selected.
     *
     * Either way of selecting tests is enough on its own to establish that:
     * a test that is in no selected group is not run whether or not its name
     * matches, and the other way round.
     *
     * @param non-empty-string       $file
     * @param list<non-empty-string> $groupsFromConfiguration
     */
    public function canSkipLoading(string $file, array $groupsFromConfiguration): bool
    {
        if (!$this->groupPruner->prunes() && !$this->nameFilterPruner->prunes()) {
            return false;
        }

        $entry = $this->index->entryFor($file);

        if ($entry === null) {
            return false;
        }

        if ($this->groupPruner->canSkip($entry, $groupsFromConfiguration)) {
            return true;
        }

        return $this->nameFilterPruner->canSkip($entry);
    }

    /**
     * Files that are already indexed are not indexed again: the entry for a
     * file is only handed out while it is still valid, so an entry that exists
     * describes the file as it is now.
     *
     * @param non-empty-string $file
     */
    public function record(string $file): void
    {
        if ($this->index->entryFor($file) !== null) {
            return;
        }

        try {
            $this->index->record((new TestSuiteLoader)->load($file));
        } catch (Exception $e) {
            /*
             * A file that does not contain a test class is not indexed and is
             * therefore always loaded, which keeps the warning PHPUnit emits
             * for it.
             */
        }
    }

    public function persist(): void
    {
        $this->index->persist();
    }
}
