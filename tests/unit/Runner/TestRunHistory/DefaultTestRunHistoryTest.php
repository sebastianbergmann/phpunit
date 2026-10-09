<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestRunHistory;

use const DIRECTORY_SEPARATOR;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function json_encode;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\DirectoryDoesNotExistException;

#[CoversClass(DefaultTestRunHistory::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-run-history')]
final class DefaultTestRunHistoryTest extends TestCase
{
    private array $filesToClean = [];

    protected function tearDown(): void
    {
        foreach ($this->filesToClean as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function testConstructorAppendsDefaultFilenameWhenPathIsDirectory(): void
    {
        $cache = new DefaultTestRunHistory(sys_get_temp_dir());

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->setStatus($id, TestStatus::failure('failure'));
        $cache->setTime($id, 1.5);

        $expectedFile         = sys_get_temp_dir() . DIRECTORY_SEPARATOR . '.phpunit.result.cache';
        $this->filesToClean[] = $expectedFile;

        $cache->persist();

        $this->assertFileExists($expectedFile);

        $loaded = new DefaultTestRunHistory(sys_get_temp_dir());
        $loaded->load();

        $this->assertTrue($loaded->status($id)->isFailure());
        $this->assertSame(1.5, $loaded->time($id));
    }

    public function testSetStatusIgnoresSuccessStatus(): void
    {
        $cache = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-success.cache');
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->setStatus($id, TestStatus::success());

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testRemoveDeletesStatus(): void
    {
        $cache = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-remove.cache');
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->setStatus($id, TestStatus::failure('failure'));

        $cache->remove($id);

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testRemoveIgnoresUnknownId(): void
    {
        $cache = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-remove.cache');
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache->remove($id);

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testLoadReturnsEarlyWhenFileDoesNotExist(): void
    {
        $cache = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-nonexistent-' . uniqid() . '.cache');

        $cache->load();

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testLoadReturnsEarlyWhenFileContainsInvalidJson(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        file_put_contents($file, 'not valid json');

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testLoadReturnsEarlyWhenVersionKeyIsMissing(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        file_put_contents($file, json_encode(['defects' => [], 'times' => []]));

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testLoadReturnsEarlyWhenVersionDoesNotMatch(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        file_put_contents($file, json_encode(['version' => 9999, 'defects' => [], 'times' => []]));

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testLoadReturnsEarlyWhenDefectsOrTimesAreMissing(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        file_put_contents($file, json_encode(['version' => 2]));

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $this->assertTrue($cache->status($id)->isUnknown());
    }

    public function testLoadIgnoresEntriesThatHaveUnexpectedTypes(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        file_put_contents(
            $file,
            json_encode(
                [
                    'version' => 2,
                    'defects' => [
                        $id->asString() => 'not an integer',
                        0               => 4,
                    ],
                    'times' => [
                        $id->asString() => 'not a float',
                        0               => 1.5,
                    ],
                ],
            ),
        );

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $this->assertTrue($cache->status($id)->isUnknown());
        $this->assertSame(0.0, $cache->time($id));
    }

    public function testPersistThrowsExceptionWhenDirectoryCannotBeCreated(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        // Use a regular file as parent directory — mkdir will fail because the parent is not a directory
        $cache = new DefaultTestRunHistory($file . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . '.phpunit.result.cache');

        $this->expectException(DirectoryDoesNotExistException::class);

        $cache->persist();
    }

    public function testPersistKeepsResultsRecordedByConcurrentTestRun(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-concurrent-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $idA = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');
        $idB = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testB');

        $one = new DefaultTestRunHistory($file);
        $one->load();
        $one->setStatus($idA, TestStatus::failure('failure in A'));
        $one->setTime($idA, 1.0);

        $other = new DefaultTestRunHistory($file);
        $other->setStatus($idB, TestStatus::error('error in B'));
        $other->setTime($idB, 2.0);
        $other->persist();

        $one->persist();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($idA)->isFailure());
        $this->assertSame(1.0, $loaded->time($idA));
        $this->assertTrue($loaded->status($idB)->isError());
        $this->assertSame(2.0, $loaded->time($idB));
    }

    public function testPersistPropagatesRemovalsToConcurrentlyPersistedFile(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-concurrent-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $idA = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');
        $idB = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testB');

        $seed = new DefaultTestRunHistory($file);
        $seed->setStatus($idA, TestStatus::failure('failed in previous run'));
        $seed->persist();

        $one = new DefaultTestRunHistory($file);
        $one->load();
        $one->remove($idA);

        $other = new DefaultTestRunHistory($file);
        $other->load();
        $other->setStatus($idB, TestStatus::error('error in B'));
        $other->persist();

        $one->persist();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($idA)->isUnknown());
        $this->assertTrue($loaded->status($idB)->isError());
    }

    public function testPersistWritesMergedEntriesToNewFile(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-concurrent-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');

        $other = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-other.cache');
        $other->setStatus($id, TestStatus::failure('failure in A'));
        $other->setTime($id, 1.0);

        $target = new DefaultTestRunHistory($file);
        $target->mergeWith($other);
        $target->persist();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($id)->isFailure());
        $this->assertSame(1.0, $loaded->time($id));
    }

    public function testPersistOverwritesInvalidFileContents(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        file_put_contents($file, 'not valid json');

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');

        $cache = new DefaultTestRunHistory($file);
        $cache->setStatus($id, TestStatus::failure('failure in A'));
        $cache->persist();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($id)->isFailure());
    }

    public function testPersistAndPruneDropsEntriesNotTouchedInThisRun(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-prune-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $idStale   = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testGone');
        $idCurrent = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testStillExists');

        $seed = new DefaultTestRunHistory($file);
        $seed->setStatus($idStale, TestStatus::failure('failed before it was deleted'));
        $seed->setTime($idStale, 1.0);
        $seed->persist();

        $cache = new DefaultTestRunHistory($file);
        $cache->load();
        $cache->setStatus($idCurrent, TestStatus::failure('still failing'));
        $cache->setTime($idCurrent, 2.0);
        $cache->persistAndPrune();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($idStale)->isUnknown());
        $this->assertSame(0.0, $loaded->time($idStale));
        $this->assertTrue($loaded->status($idCurrent)->isFailure());
        $this->assertSame(2.0, $loaded->time($idCurrent));
    }

    public function testPersistAndPruneKeepsTimeOfTestThatWasSkippedInThisRun(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-prune-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testSkippedThisTime');

        $seed = new DefaultTestRunHistory($file);
        $seed->setTime($id, 5.0);
        $seed->persist();

        $cache = new DefaultTestRunHistory($file);
        $cache->load();
        $cache->setStatus($id, TestStatus::skipped('not applicable'));
        $cache->persistAndPrune();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($id)->isSkipped());
        $this->assertSame(5.0, $loaded->time($id));
    }

    public function testPersistAndPruneKeepsTimeOfTestThatPassedInThisRun(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-prune-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testFixed');

        $seed = new DefaultTestRunHistory($file);
        $seed->setStatus($id, TestStatus::failure('failed in previous run'));
        $seed->setTime($id, 5.0);
        $seed->persist();

        $cache = new DefaultTestRunHistory($file);
        $cache->load();
        $cache->remove($id);
        $cache->setTime($id, 1.0);
        $cache->persistAndPrune();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertTrue($loaded->status($id)->isUnknown());
        $this->assertSame(1.0, $loaded->time($id));
    }

    public function testMergeWithCombinesDefectsAndTimes(): void
    {
        $target = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-target.cache');
        $other  = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-other.cache');

        $idA = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');
        $idB = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testB');
        $idC = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testC');

        $target->setStatus($idA, TestStatus::failure('failure in A'));
        $target->setTime($idA, 1.0);

        $other->setStatus($idB, TestStatus::error('error in B'));
        $other->setTime($idB, 2.0);
        $other->setTime($idC, 3.0);

        $target->mergeWith($other);

        $this->assertTrue($target->status($idA)->isFailure());
        $this->assertSame(1.0, $target->time($idA));
        $this->assertTrue($target->status($idB)->isError());
        $this->assertSame(2.0, $target->time($idB));
        $this->assertSame(3.0, $target->time($idC));
    }

    public function testMergeWithOverwritesExistingEntries(): void
    {
        $target = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-target.cache');
        $other  = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-other.cache');

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');

        $target->setStatus($id, TestStatus::failure('old failure'));
        $target->setTime($id, 1.0);

        $other->setStatus($id, TestStatus::error('new error'));
        $other->setTime($id, 5.0);

        $target->mergeWith($other);

        $this->assertTrue($target->status($id)->isError());
        $this->assertSame(5.0, $target->time($id));
    }

    public function testRemembersAndForgetsTheCounterexampleOfAProperty(): void
    {
        $cache = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-counterexample.cache');
        $id    = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testProperty');

        $this->assertNull($cache->counterexample($id));

        $cache->setCounterexample($id, [0, 11]);

        $this->assertSame([0, 11], $cache->counterexample($id));

        $cache->removeCounterexample($id);

        $this->assertNull($cache->counterexample($id));
    }

    public function testPersistsAndLoadsCounterexamples(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-counterexamples-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testProperty');

        $cache = new DefaultTestRunHistory($file);
        $cache->setCounterexample($id, [0, 11]);
        $cache->persist();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertSame([0, 11], $loaded->counterexample($id));
    }

    public function testDoesNotWriteCounterexamplesWhenNoneAreRemembered(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-counterexamples-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $cache = new DefaultTestRunHistory($file);
        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne'), 1.0);
        $cache->persist();

        $this->assertStringNotContainsString('counterexamples', (string) file_get_contents($file));
    }

    public function testLoadsTestRunHistoryWithoutCounterexamples(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        file_put_contents($file, json_encode(['version' => 2, 'defects' => [], 'times' => [self::class . '::testOne' => 1.0]]));

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testOne');

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $this->assertSame(1.0, $cache->time($id));
        $this->assertNull($cache->counterexample($id));
    }

    public function testLoadIgnoresCounterexamplesThatHaveUnexpectedTypes(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        $prefix = self::class . '::';

        file_put_contents(
            $file,
            json_encode(
                [
                    'version'         => 2,
                    'defects'         => [],
                    'times'           => [],
                    'counterexamples' => [
                        $prefix . 'testValid'          => [0, 11],
                        $prefix . 'testNotAList'       => ['a' => 1],
                        $prefix . 'testNotAnArray'     => 1,
                        $prefix . 'testNegativeChoice' => [0, -1],
                        $prefix . 'testChoiceNotAnInt' => [0, '1'],
                        '123'                          => [0],
                    ],
                ],
            ),
        );

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $this->assertSame([0, 11], $cache->counterexample(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testValid')));
        $this->assertNull($cache->counterexample(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testNotAList')));
        $this->assertNull($cache->counterexample(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testNotAnArray')));
        $this->assertNull($cache->counterexample(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testNegativeChoice')));
        $this->assertNull($cache->counterexample(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testChoiceNotAnInt')));
    }

    public function testLoadIgnoresCounterexamplesThatAreNotAnArray(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        file_put_contents($file, json_encode(['version' => 2, 'defects' => [], 'times' => [], 'counterexamples' => 'invalid']));

        $cache = new DefaultTestRunHistory($file);
        $cache->load();

        $this->assertNull($cache->counterexample(TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testProperty')));
    }

    public function testPersistAndPruneKeepsCounterexampleOfPropertyThatRanInThisRunAndDropsTheOthers(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-prune-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $idStale   = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testGone');
        $idSkipped = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testSkippedThisTime');
        $idNew     = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testFalsifiedThisTime');

        $seed = new DefaultTestRunHistory($file);
        $seed->setCounterexample($idStale, [1]);
        $seed->setCounterexample($idSkipped, [2]);
        $seed->persist();

        $cache = new DefaultTestRunHistory($file);
        $cache->load();
        $cache->setStatus($idSkipped, TestStatus::skipped('not applicable'));
        $cache->setCounterexample($idNew, [3]);
        $cache->persistAndPrune();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertNull($loaded->counterexample($idStale));
        $this->assertSame([2], $loaded->counterexample($idSkipped));
        $this->assertSame([3], $loaded->counterexample($idNew));
    }

    public function testPersistOverlaysChangedCounterexamplesOntoConcurrentlyPersistedFile(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-concurrent-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $idA = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');
        $idB = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testB');
        $idC = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testC');

        $seed = new DefaultTestRunHistory($file);
        $seed->setCounterexample($idB, [2]);
        $seed->persist();

        $one = new DefaultTestRunHistory($file);
        $one->load();
        $one->setCounterexample($idA, [1]);
        $one->removeCounterexample($idB);

        $other = new DefaultTestRunHistory($file);
        $other->load();
        $other->setCounterexample($idC, [3]);
        $other->persist();

        $one->persist();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $this->assertSame([1], $loaded->counterexample($idA));
        $this->assertNull($loaded->counterexample($idB));
        $this->assertSame([3], $loaded->counterexample($idC));
    }

    public function testMergeWithCombinesCounterexamplesAndPropagatesTheirRemoval(): void
    {
        $target = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-target.cache');
        $other  = new DefaultTestRunHistory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-other.cache');

        $idA = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testA');
        $idB = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testB');
        $idC = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testC');

        $target->setCounterexample($idA, [1]);
        $target->setCounterexample($idB, [2]);

        $other->removeCounterexample($idB);
        $other->setCounterexample($idC, [3]);

        $target->mergeWith($other);

        $this->assertSame([1], $target->counterexample($idA));
        $this->assertNull($target->counterexample($idB));
        $this->assertSame([3], $target->counterexample($idC));
    }

    public function testMergeWithDoesNotBringBackACounterexampleThatAnotherMergedTestRunHistoryRemoved(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-merge-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testProperty');

        $seed = new DefaultTestRunHistory($file);
        $seed->setCounterexample($id, [1]);
        $seed->persist();

        $removed = new DefaultTestRunHistory($file);
        $removed->load();
        $removed->removeCounterexample($id);

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $removedFirst = new DefaultTestRunHistory($file);
        $removedFirst->mergeWith($removed);
        $removedFirst->mergeWith($loaded);

        $loadedFirst = new DefaultTestRunHistory($file);
        $loadedFirst->mergeWith($loaded);
        $loadedFirst->mergeWith($removed);

        $this->assertNull($removedFirst->counterexample($id));
        $this->assertNull($loadedFirst->counterexample($id));
    }

    public function testMergeWithTakesOverACounterexampleThatTheOtherTestRunHistoryOnlyLoaded(): void
    {
        $file                 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-merge-' . uniqid() . '.cache';
        $this->filesToClean[] = $file;

        $id = TestRunHistoryId::fromTestClassAndMethodName(self::class, 'testProperty');

        $seed = new DefaultTestRunHistory($file);
        $seed->setCounterexample($id, [1]);
        $seed->persist();

        $loaded = new DefaultTestRunHistory($file);
        $loaded->load();

        $target = new DefaultTestRunHistory($file);
        $target->mergeWith($loaded);

        $this->assertSame([1], $target->counterexample($id));
    }
}
