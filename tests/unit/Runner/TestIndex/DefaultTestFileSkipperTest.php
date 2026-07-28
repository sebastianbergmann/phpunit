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

use const DIRECTORY_SEPARATOR;
use function array_keys;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\TestRunner\WarningTriggered;
use PHPUnit\Event\TestRunner\WarningTriggeredSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\TestFileSkipper\WarnedTest;
use ReflectionClass;

#[CoversClass(DefaultTestFileSkipper::class)]
#[UsesClass(TestIndex::class)]
#[UsesClass(TestIndexEntry::class)]
#[UsesClass(GroupPruner::class)]
#[UsesClass(FileHasher::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class DefaultTestFileSkipperTest extends AbstractEventTestCase
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

    #[TestDox('Does not skip a file that is not indexed')]
    public function testDoesNotSkipFileThatIsNotIndexed(): void
    {
        $file = $this->writeTestClass('NotIndexed');

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Skips an indexed file without a test in the selected group')]
    public function testSkipsIndexedFileWithoutTestInSelectedGroup(): void
    {
        $file = $this->writeTestClass('NotSelected');

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();

        $this->assertTrue($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Does not skip an indexed file with a test in the selected group')]
    public function testDoesNotSkipIndexedFileWithTestInSelectedGroup(): void
    {
        $file = $this->writeTestClass('Selected');

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($this->directory()),
            new GroupPruner(['a-group'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Does not skip anything when no groups are selected')]
    public function testDoesNotSkipAnythingWhenNoGroupsAreSelected(): void
    {
        $file = $this->writeTestClass('NoSelection');

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($this->directory()),
            new GroupPruner([], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Considers the groups configured for a test file in the XML configuration file')]
    public function testConsidersGroupsFromConfiguration(): void
    {
        $file = $this->writeTestClass('FromConfiguration');

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($this->directory()),
            new GroupPruner(['from-configuration'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();

        $this->assertTrue($skipper->canSkipLoading($file, []));
        $this->assertFalse($skipper->canSkipLoading($file, ['from-configuration']));
    }

    #[TestDox('Does not index a file that does not contain a test class')]
    public function testDoesNotIndexFileThatDoesNotContainTestClass(): void
    {
        $directory = $this->directory();
        $file      = $directory . DIRECTORY_SEPARATOR . 'NotATest.php';

        file_put_contents($file, '<?php declare(strict_types=1);' . "\n");

        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();
        $skipper->persist();

        $this->assertFalse($skipper->canSkipLoading($file, []));
        $this->assertSame([], $this->entriesIn($indexDirectory));
    }

    #[TestDox('Does not index a file again while what is known about it is still valid')]
    public function testDoesNotIndexFileAgainWhileWhatIsKnownAboutItIsStillValid(): void
    {
        $file           = $this->writeTestClass('AlreadyIndexed');
        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();
        $skipper->persist();

        $before = file_get_contents($indexDirectory . DIRECTORY_SEPARATOR . 'test-index');

        $skipper->startRecording($file);
        $skipper->stopRecording();
        $skipper->persist();

        $this->assertSame($before, file_get_contents($indexDirectory . DIRECTORY_SEPARATOR . 'test-index'));
    }

    public function testWritesTheIndex(): void
    {
        $file           = $this->writeTestClass('Written');
        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();
        $skipper->persist();

        $this->assertSame([$file], array_keys($this->entriesIn($indexDirectory)));
    }

    #[TestDox('Does not index a file that could not be loaded, and indexes the files after it')]
    public function testDoesNotIndexFileThatCouldNotBeLoaded(): void
    {
        $aborted        = $this->writeTestClass('Aborted');
        $loaded         = $this->writeTestClass('Loaded');
        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            new EventFacade,
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($aborted);
        $skipper->abortRecording();

        /*
         * The events emitted while the next file is loaded are only collected
         * when the collecting that was started for the file before it ended.
         */
        $skipper->startRecording($loaded);
        $skipper->stopRecording();
        $skipper->persist();

        $this->assertFalse($skipper->canSkipLoading($aborted, []));
        $this->assertSame([$loaded], array_keys($this->entriesIn($indexDirectory)));
    }

    #[TestDox('Forwards what was collected for a file that could not be loaded, and stops collecting')]
    public function testForwardsCollectedEventsWhenFileCouldNotBeLoaded(): void
    {
        $subscriber = new class implements WarningTriggeredSubscriber
        {
            /**
             * @var list<string>
             */
            public array $messages = [];

            public function notify(WarningTriggered $event): void
            {
                $this->messages[] = $event->message();
            }
        };

        $eventFacade = new EventFacade;
        $eventFacade->registerSubscriber($subscriber);
        $eventFacade->seal();

        $skipper = new DefaultTestFileSkipper(
            $eventFacade,
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($this->writeTestClass('Collected'));

        $eventFacade->forward($this->warning('while the file was being loaded'));

        $this->assertSame([], $subscriber->messages);

        $skipper->abortRecording();

        $this->assertSame(['while the file was being loaded'], $subscriber->messages);

        // what is emitted afterwards is no longer collected for a file that is no longer being loaded
        $eventFacade->forward($this->warning('after loading the file failed'));

        $this->assertSame(['while the file was being loaded', 'after loading the file failed'], $subscriber->messages);
    }

    #[TestDox('Does not skip a file PHPUnit warned about while it was loaded')]
    public function testDoesNotSkipFileThatPhpUnitWarnedAbout(): void
    {
        $file = $this->writeTestClass('Warned');

        require_once $file;

        $index = new TestIndex($this->directory());
        $index->record(new ReflectionClass(WarnedTest::class), true);

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            $index,
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Does not index a PHPT file, which must never be loaded as PHP')]
    public function testDoesNotIndexPhptFile(): void
    {
        $file           = $this->writePhptFile('a-test');
        $indexDirectory = $this->directory();

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($indexDirectory),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        $skipper->stopRecording();
        $skipper->persist();

        $this->assertFalse($skipper->canSkipLoading($file, []));
        $this->assertSame([], $this->entriesIn($indexDirectory));
    }

    #[TestDox('Does nothing when no test file is being loaded')]
    public function testDoesNothingWhenNoTestFileIsBeingLoaded(): void
    {
        $subscriber = new class implements WarningTriggeredSubscriber
        {
            /**
             * @var list<string>
             */
            public array $messages = [];

            public function notify(WarningTriggered $event): void
            {
                $this->messages[] = $event->message();
            }
        };

        $eventFacade = new EventFacade;
        $eventFacade->registerSubscriber($subscriber);
        $eventFacade->seal();

        $skipper = new DefaultTestFileSkipper(
            $eventFacade,
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->stopRecording();
        $skipper->abortRecording();

        // nothing is being collected, so what is emitted reaches its subscriber
        $eventFacade->forward($this->warning('while no file was being loaded'));

        $this->assertSame(['while no file was being loaded'], $subscriber->messages);
    }

    #[TestDox('Does not skip a file PHPUnit had something to say about while it was loaded')]
    public function testDoesNotSkipFileThatPhpUnitHadSomethingToSayAbout(): void
    {
        $file = $this->writeTestClass('SomethingToSay');

        $eventFacade = new EventFacade;
        $eventFacade->seal();

        $skipper = new DefaultTestFileSkipper(
            $eventFacade,
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);

        $eventFacade->forward($this->warning('while the file was being loaded'));

        $skipper->stopRecording();

        $this->assertFalse($skipper->canSkipLoading($file, []));
    }

    #[TestDox('Skips a file whose data providers were called while it was loaded')]
    public function testSkipsFileWhoseDataProvidersWereCalled(): void
    {
        $file = $this->writeTestClassWithDataProvider('Provider');

        $skipper = new DefaultTestFileSkipper(
            EventFacade::instance(),
            new TestIndex($this->directory()),
            new GroupPruner(['other'], []),
            NameFilterPruner::withoutFilter(),
        );

        $skipper->startRecording($file);
        TestSuite::empty('test')->addTestFile($file);
        $skipper->stopRecording();

        $this->assertTrue($skipper->canSkipLoading($file, []));
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writePhptFile(string $name): string
    {
        $file = $this->directory() . DIRECTORY_SEPARATOR . $name . '.phpt';

        file_put_contents(
            $file,
            <<<'PHPT'
                --TEST--
                A PHPT test
                --FILE--
                <?php declare(strict_types=1);
                print 'the PHPT test was run';
                --EXPECT--
                the PHPT test was run
                PHPT,
        );

        return $file;
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestClassWithDataProvider(string $name): string
    {
        $file = $this->directory() . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestFileSkipper;

                use PHPUnit\Framework\Attributes\DataProvider;
                use PHPUnit\Framework\Attributes\Group;
                use PHPUnit\Framework\TestCase;

                final class {$name}Test extends TestCase
                {
                    public static function provider(): array
                    {
                        return [[true]];
                    }

                    #[DataProvider('provider')]
                    #[Group('a-group')]
                    public function testOne(bool \$value): void
                    {
                    }
                }
                PHP,
        );

        return $file;
    }

    /**
     * @param non-empty-string $message
     */
    private function warning(string $message): EventCollection
    {
        $events = new EventCollection;

        $events->add(new WarningTriggered($this->telemetryInfo(), $message));

        return $events;
    }

    /**
     * @param non-empty-string $directory
     *
     * @return array<string, mixed>
     */
    private function entriesIn(string $directory): array
    {
        $contents = file_get_contents($directory . DIRECTORY_SEPARATOR . 'test-index');

        $this->assertIsString($contents);

        $data = json_decode($contents, true);

        $this->assertIsArray($data);
        $this->assertIsArray($data['entries']);

        return $data['entries'];
    }

    /**
     * @return non-empty-string
     */
    private function directory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-skipper-' . uniqid();

        mkdir($directory);

        /*
         * The path is resolved so that it is the path PHP itself reports for
         * the files in this directory: sys_get_temp_dir() can return a path
         * that is not resolved, a short (8.3) path on Windows for instance,
         * while reflection always reports the resolved one.
         */
        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private function writeTestClass(string $name): string
    {
        $file = $this->directory() . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestFileSkipper;

                use PHPUnit\Framework\Attributes\Group;
                use PHPUnit\Framework\TestCase;

                final class {$name}Test extends TestCase
                {
                    #[Group('a-group')]
                    public function testOne(): void
                    {
                    }
                }
                PHP,
        );

        return $file;
    }
}
