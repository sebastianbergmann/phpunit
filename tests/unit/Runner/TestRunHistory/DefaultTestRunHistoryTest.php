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
}
