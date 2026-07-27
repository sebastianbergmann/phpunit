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

use function is_file;
use function str_ends_with;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Test\DataProviderMethodCalled;
use PHPUnit\Event\Test\DataProviderMethodFinished;
use PHPUnit\Runner\Exception;
use PHPUnit\Runner\TestSuiteLoader;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DefaultTestFileSkipper implements TestFileSkipper
{
    private readonly TestIndex $index;
    private readonly GroupPruner $groupPruner;
    private readonly NameFilterPruner $nameFilterPruner;

    /**
     * @var ?non-empty-string
     */
    private ?string $recording = null;

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

        if ($entry->madePhpUnitWarn()) {
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
     * A PHPT file is not a PHP file and is never loaded as one, so it is not
     * indexed either. The condition for that is the one that TestSuite uses to
     * decide whether a file is loaded as PHP.
     *
     * @param non-empty-string $file
     */
    public function startRecording(string $file): void
    {
        if (str_ends_with($file, '.phpt') && is_file($file)) {
            return;
        }

        if ($this->index->entryFor($file) !== null) {
            return;
        }

        $this->recording = $file;

        EventFacade::instance()->startCollectingEvents();
    }

    public function stopRecording(): void
    {
        if ($this->recording === null) {
            return;
        }

        $file            = $this->recording;
        $this->recording = null;

        $events = EventFacade::instance()->stopCollectingEvents();

        EventFacade::instance()->forward($events);

        try {
            $this->index->record((new TestSuiteLoader)->load($file), self::madePhpUnitWarn($events));
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

    /**
     * Loading a test file emits an event for every data provider method that
     * was called. Any other event means PHPUnit had something to say about the
     * file, and a file PHPUnit has something to say about is never skipped: it
     * would otherwise depend on the state of the index whether that is said,
     * and the same command would not produce the same output twice.
     *
     * Treating an unknown event as something to say keeps this true for events
     * that do not exist yet.
     */
    private static function madePhpUnitWarn(EventCollection $events): bool
    {
        foreach ($events as $event) {
            if ($event instanceof DataProviderMethodCalled || $event instanceof DataProviderMethodFinished) {
                continue;
            }

            return true;
        }

        return false;
    }
}
