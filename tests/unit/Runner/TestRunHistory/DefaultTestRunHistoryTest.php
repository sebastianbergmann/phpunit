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

    public function testPersistThrowsExceptionWhenDirectoryCannotBeCreated(): void
    {
        $file                 = tempnam(sys_get_temp_dir(), 'phpunit-cache-');
        $this->filesToClean[] = $file;

        // Use a regular file as parent directory — mkdir will fail because the parent is not a directory
        $cache = new DefaultTestRunHistory($file . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . '.phpunit.result.cache');

        $this->expectException(DirectoryDoesNotExistException::class);

        $cache->persist();
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
