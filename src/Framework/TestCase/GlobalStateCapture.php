<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use const PHP_EOL;
use function array_keys;
use function array_reverse;
use function count;
use function defined;
use function is_string;
use function restore_exception_handler;
use function set_exception_handler;
use function str_starts_with;
use function trim;
use PHPUnit\Event;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\Util\DifferBuilder;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\GlobalState\ExcludeList as GlobalStateExcludeList;
use SebastianBergmann\GlobalState\Restorer;
use SebastianBergmann\GlobalState\Snapshot;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GlobalStateCapture
{
    private ?bool $backupGlobals = null;

    /**
     * @var list<string>
     */
    private array $backupGlobalsExcludeList = [];
    private ?bool $backupStaticProperties   = null;

    /**
     * @var array<class-string, list<non-empty-string>>
     */
    private array $backupStaticPropertiesExcludeList = [];
    private ?Snapshot $snapshot                      = null;

    /**
     * @var null|list<callable>
     */
    private ?array $backupGlobalExceptionHandlers = null;

    public function setBackupGlobals(bool $backupGlobals): void
    {
        $this->backupGlobals = $backupGlobals;
    }

    /**
     * @param list<string> $backupGlobalsExcludeList
     */
    public function setBackupGlobalsExcludeList(array $backupGlobalsExcludeList): void
    {
        $this->backupGlobalsExcludeList = $backupGlobalsExcludeList;
    }

    public function setBackupStaticProperties(bool $backupStaticProperties): void
    {
        $this->backupStaticProperties = $backupStaticProperties;
    }

    /**
     * @param array<class-string, list<non-empty-string>> $backupStaticPropertiesExcludeList
     */
    public function setBackupStaticPropertiesExcludeList(array $backupStaticPropertiesExcludeList): void
    {
        $this->backupStaticPropertiesExcludeList = $backupStaticPropertiesExcludeList;
    }

    /**
     * @throws Throwable
     */
    public function snapshotGlobals(TestCase $test, Event\Emitter $emitter, bool $inIsolation, ?bool $runTestInSeparateProcess): void
    {
        if ($runTestInSeparateProcess === true || $inIsolation ||
            ($this->backupGlobals !== true && $this->backupStaticProperties !== true)) {
            return;
        }

        $this->snapshot = $this->createSnapshot($test, $emitter, $this->backupGlobals === true);
    }

    /**
     * @throws Throwable
     */
    public function restoreGlobals(TestCase $test, Event\Emitter $emitter): void
    {
        $snapshot = $this->snapshot;

        if (!$snapshot instanceof Snapshot) {
            return;
        }

        if (ConfigurationRegistry::get()->beStrictAboutChangesToGlobalState()) {
            $this->compareSnapshots(
                $test,
                $emitter,
                $snapshot,
                $this->createSnapshot($test, $emitter, $this->backupGlobals === true),
            );
        }

        $restorer = new Restorer;

        if ($this->backupGlobals === true) {
            $restorer->restoreGlobalVariables($snapshot);
        }

        if ($this->backupStaticProperties === true) {
            $restorer->restoreStaticProperties($snapshot);
        }

        $this->snapshot = null;
    }

    public function snapshotErrorHandlers(TestCase $test, Event\Emitter $emitter): void
    {
        foreach (ErrorHandler::instance()->snapshotErrorHandlers() as $message) {
            $emitter->testConsideredRisky(
                $test->valueObjectForEvents(),
                $message,
            );
        }

        $this->backupGlobalExceptionHandlers = self::activeExceptionHandlers();
    }

    public function restoreErrorHandlers(TestCase $test, Event\Emitter $emitter, bool $inIsolation): void
    {
        foreach (ErrorHandler::instance()->restoreErrorHandlers($inIsolation) as $message) {
            $emitter->testConsideredRisky(
                $test->valueObjectForEvents(),
                $message,
            );
        }

        $activeExceptionHandlers = self::activeExceptionHandlers();

        $message = null;

        $backupGlobalExceptionHandlers = $this->backupGlobalExceptionHandlers;

        if ($backupGlobalExceptionHandlers !== null && $activeExceptionHandlers !== $backupGlobalExceptionHandlers) {
            if (count($activeExceptionHandlers) > count($backupGlobalExceptionHandlers)) {
                if (!$inIsolation) {
                    $message = 'Test code or tested code did not remove its own exception handlers';
                }
            } else {
                $message = 'Test code or tested code removed exception handlers other than its own';
            }

            foreach ($activeExceptionHandlers as $handler) {
                restore_exception_handler();
            }

            foreach ($backupGlobalExceptionHandlers as $handler) {
                set_exception_handler($handler);
            }
        }

        $this->backupGlobalExceptionHandlers = null;

        if ($message !== null) {
            $emitter->testConsideredRisky(
                $test->valueObjectForEvents(),
                $message,
            );
        }
    }

    /**
     * @throws Throwable
     */
    public function createSnapshot(TestCase $test, Event\Emitter $emitter, bool $backupGlobals): Snapshot
    {
        $excludeList = new GlobalStateExcludeList;

        foreach ($this->backupGlobalsExcludeList as $globalVariable) {
            if ($globalVariable !== '') {
                $excludeList->addGlobalVariable($globalVariable);
            }
        }

        foreach ($this->backupStaticPropertiesExcludeList as $class => $properties) {
            foreach ($properties as $property) {
                $excludeList->addStaticProperty($class, $property);
            }
        }

        if (!defined('PHPUNIT_TESTSUITE')) {
            $excludeList->addClassNamePrefix('PHPUnit');
            $excludeList->addClassNamePrefix('SebastianBergmann\CodeCoverage');
            $excludeList->addClassNamePrefix('SebastianBergmann\FileIterator');
            $excludeList->addClassNamePrefix('SebastianBergmann\Invoker');
            $excludeList->addClassNamePrefix('SebastianBergmann\Template');
            $excludeList->addClassNamePrefix('SebastianBergmann\Timer');

            foreach (array_keys($GLOBALS) as $key) {
                if (is_string($key) && str_starts_with($key, '__phpunit_')) {
                    $excludeList->addGlobalVariable($key);
                }
            }

            $excludeList->addStaticProperty(ComparatorFactory::class, 'instance');
        }

        try {
            return new Snapshot(
                $excludeList,
                $backupGlobals,
                (bool) $this->backupStaticProperties,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
            );
        } catch (Throwable $t) {
            $emitter->testPreparationFailed(
                $test->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($t),
            );

            $emitter->testErrored(
                $test->valueObjectForEvents(),
                Event\Code\ThrowableBuilder::from($t),
            );

            throw $t;
        }
    }

    private function compareSnapshots(TestCase $test, Event\Emitter $emitter, Snapshot $before, Snapshot $after): void
    {
        $backupGlobals = $this->backupGlobals === null || $this->backupGlobals;

        if ($backupGlobals) {
            self::compareSnapshotPart(
                $test,
                $emitter,
                $before->globalVariables(),
                $after->globalVariables(),
                "--- Global variables before the test\n+++ Global variables after the test\n",
            );

            self::compareSnapshotPart(
                $test,
                $emitter,
                $before->superGlobalVariables(),
                $after->superGlobalVariables(),
                "--- Super-global variables before the test\n+++ Super-global variables after the test\n",
            );
        }

        if ($this->backupStaticProperties === true) {
            self::compareSnapshotPart(
                $test,
                $emitter,
                $before->staticProperties(),
                $after->staticProperties(),
                "--- Static properties before the test\n+++ Static properties after the test\n",
            );
        }
    }

    /**
     * @param array<mixed>     $before
     * @param array<mixed>     $after
     * @param non-empty-string $header
     */
    private static function compareSnapshotPart(TestCase $test, Event\Emitter $emitter, array $before, array $after, string $header): void
    {
        if ($before === $after) {
            return;
        }

        $differ = DifferBuilder::build($header);

        $emitter->testConsideredRisky(
            $test->valueObjectForEvents(),
            'This test modified global state but was not expected to do so' . PHP_EOL .
            trim(
                $differ->diff(
                    Exporter::export($before),
                    Exporter::export($after),
                ),
            ),
        );
    }

    /**
     * @return list<callable>
     */
    private static function activeExceptionHandlers(): array
    {
        $res = [];

        while (true) {
            $previousHandler = set_exception_handler(static fn () => null);
            restore_exception_handler();

            if ($previousHandler === null) {
                break;
            }
            $res[] = $previousHandler;
            restore_exception_handler();
        }
        $res = array_reverse($res);

        foreach ($res as $handler) {
            set_exception_handler($handler);
        }

        return $res;
    }
}
