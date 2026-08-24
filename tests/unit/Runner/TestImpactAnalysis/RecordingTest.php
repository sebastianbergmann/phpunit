<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use const DIRECTORY_SEPARATOR;
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\TestIndex\FileHasher;

#[CoversClass(Recording::class)]
#[UsesClass(PathHasher::class)]
#[UsesClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class RecordingTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            $entries = scandir($directory);

            if ($entries !== false) {
                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    unlink($directory . DIRECTORY_SEPARATOR . $entry);
                }
            }

            rmdir($directory);
        }

        $this->directories = [];
    }

    public function testKnowsThatNothingWasRecorded(): void
    {
        $this->assertTrue(Recording::from([], [], [], [])->isEmpty());
    }

    public function testKnowsWhichTestsWereRecorded(): void
    {
        $recording = Recording::from(['/src/Foo.php'], [[0, 'a-hash']], ['FooTest::testOne' => [0]], []);

        $this->assertFalse($recording->isEmpty());
        $this->assertTrue($recording->knows('FooTest::testOne'));
        $this->assertFalse($recording->knows('BarTest::testOne'));
    }

    public function testKnowsWhichTestsDependOnSomethingThatIsNotWhatItWas(): void
    {
        $directory = $this->temporaryDirectory();
        $unchanged = $this->writeFile($directory, 'Unchanged.php', 'first');
        $changed   = $this->writeFile($directory, 'Changed.php', 'first');

        $hasher    = new PathHasher;
        $recording = Recording::from(
            [$unchanged, $changed],
            [[0, $this->hashOf($unchanged)], [1, $this->hashOf($changed)]],
            [
                'FooTest::testOne' => [0],
                'BarTest::testOne' => [1],
                'BazTest::testOne' => [0, 1],
            ],
            [],
        );

        $this->writeFile($directory, 'Changed.php', 'second');

        $this->assertSame(
            [
                'BarTest::testOne' => true,
                'BazTest::testOne' => true,
            ],
            $recording->testsAffectedByWhatChanged($hasher),
        );
    }

    public function testKnowsThatASourceFileThatWasNotRecordedIsAChangeNothingIsKnownAbout(): void
    {
        $recording = Recording::from(['/src/Foo.php'], [[0, 'a-hash']], ['FooTest::testOne' => [0]], []);

        $this->assertStringContainsString(
            '/src/Bar.php was not there',
            (string) $recording->changeNothingIsKnownAbout(new PathHasher, ['/src/Foo.php', '/src/Bar.php']),
        );
    }

    public function testKnowsThatAChangeToAFileNoTestDependsOnIsAChangeNothingIsKnownAbout(): void
    {
        $directory = $this->temporaryDirectory();
        $covered   = $this->writeFile($directory, 'Covered.php', 'first');
        $untested  = $this->writeFile($directory, 'Untested.php', 'first');

        $recording = Recording::from(
            [$covered, $untested],
            [[0, $this->hashOf($covered)]],
            ['FooTest::testOne' => [0]],
            [
                0 => $this->hashOf($covered),
                1 => $this->hashOf($untested),
            ],
        );

        $this->assertNull($recording->changeNothingIsKnownAbout(new PathHasher, [$covered, $untested]));

        $this->writeFile($directory, 'Untested.php', 'second');

        $this->assertStringContainsString(
            'Untested.php changed and no test is recorded as depending on it',
            (string) $recording->changeNothingIsKnownAbout(new PathHasher, [$covered, $untested]),
        );
    }

    /**
     * @return non-empty-string
     */
    private function hashOf(string $file): string
    {
        $hash = (new PathHasher)->hash($file);

        $this->assertIsString($hash);
        $this->assertNotSame('', $hash);

        return $hash;
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-recording-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @return non-empty-string
     */
    private function writeFile(string $directory, string $name, string $contents): string
    {
        $file = $directory . DIRECTORY_SEPARATOR . $name;

        file_put_contents($file, $contents);

        return $file;
    }
}
