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
use Closure;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Test\DataProviderMethodCalled;
use PHPUnit\Event\Test\DataProviderMethodFinished;
use PHPUnit\Runner\Exception;
use PHPUnit\Runner\TestSuiteLoader;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DefaultTestFileSkipper implements TestFileSkipper
{
    private readonly EventFacade $eventFacade;
    private readonly TestIndex $index;
    private readonly GroupPruner $groupPruner;
    private readonly NameFilterPruner $nameFilterPruner;

    /**
     * @var ?non-empty-string
     */
    private ?string $recording = null;

    public function __construct(EventFacade $eventFacade, TestIndex $index, GroupPruner $groupPruner, NameFilterPruner $nameFilterPruner)
    {
        $this->eventFacade      = $eventFacade;
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
        /*
         * Both pruners answer this on their own as well. Asking them here keeps
         * a run that selects tests neither by group nor by name from looking up
         * the entry for every test file: no file can be skipped then, and
         * finding out whether an entry is still valid means hashing every file
         * it was derived from.
         */
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
     * @template T
     *
     * @param non-empty-string $file
     * @param Closure(): T     $load
     *
     * @throws Throwable
     *
     * @return T
     */
    public function record(string $file, Closure $load): mixed
    {
        $this->startRecording($file);

        try {
            $result = $load();
        } catch (Throwable $t) {
            $this->abortRecording();

            throw $t;
        }

        $this->stopRecording();

        return $result;
    }

    public function persist(): void
    {
        $this->index->persist();
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
    private function startRecording(string $file): void
    {
        if (str_ends_with($file, '.phpt') && is_file($file)) {
            return;
        }

        if ($this->index->entryFor($file) !== null) {
            return;
        }

        $this->recording = $file;

        $this->eventFacade->startCollectingEvents();
    }

    private function stopRecording(): void
    {
        if ($this->recording === null) {
            return;
        }

        $file            = $this->recording;
        $this->recording = null;

        $events = $this->eventFacade->stopCollectingEvents();

        $this->eventFacade->forward($events);

        try {
            $this->index->record((new TestSuiteLoader)->load($file), self::madePhpUnitWarn($events));
        } catch (Exception) {
            /*
             * A file that does not contain a test class is not indexed and is
             * therefore always loaded, which keeps the warning PHPUnit emits
             * for it. Loading it is also the only way of finding that out: the
             * test suite the file was added to reports it as a warning instead
             * of letting it end the run.
             */
        }
    }

    /**
     * What was collected while the file was being loaded is still forwarded:
     * the file is not indexed, but what PHPUnit had to say about it before
     * loading it failed must not be swallowed. Leaving the event facade in the
     * state it was in before is what makes a failure that is reported instead
     * of ending the run harmless.
     */
    private function abortRecording(): void
    {
        if ($this->recording === null) {
            return;
        }

        $this->recording = null;

        $this->eventFacade->forward($this->eventFacade->stopCollectingEvents());
    }

    /**
     * Loading a test file emits an event for every data provider method that
     * was called. Any other event means PHPUnit had something to say about the
     * file, and a file PHPUnit has something to say about is never skipped: it
     * would otherwise depend on the state of the index whether that is said.
     *
     * Treating an unknown event as something to say keeps this true for events
     * that do not exist yet.
     *
     * What this keeps the same is what PHPUnit reports about a run and which
     * tests it runs, not the events the run emits. A skipped file runs none of
     * its data provider methods, so the events for them are not emitted either.
     * The number of tests a test suite has before it is filtered depends on the
     * index as well, and does so for every skipped file rather than only for
     * the ones that have a data provider. Both are visible to --debug and to
     * the event loggers.
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
