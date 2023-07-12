<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use function assert;
use function file;
use function is_file;
use function sha1;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Runner\FileDoesNotExistException;
use PHPUnit\TextUI\Configuration\Source;
use PHPUnit\TextUI\Configuration\SourceFilter;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Generator
{
    private Baseline $baseline;
    private readonly Source $source;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Facade $facade, Source $source)
    {
        $facade->registerSubscribers(
            new TestTriggeredDeprecationSubscriber($this),
            new TestTriggeredNoticeSubscriber($this),
            new TestTriggeredPhpDeprecationSubscriber($this),
            new TestTriggeredPhpNoticeSubscriber($this),
            new TestTriggeredPhpWarningSubscriber($this),
            new TestTriggeredWarningSubscriber($this),
        );

        $this->baseline = new Baseline;
        $this->source   = $source;
    }

    public function baseline(): Baseline
    {
        return $this->baseline;
    }

    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function testTriggeredDeprecation(DeprecationTriggered $event): void
    {
        if (!$this->source->ignoreSuppressionOfDeprecations() && $event->wasSuppressed()) {
            return;
        }

        if ($this->source->restrictDeprecations() && !(new SourceFilter)->includes($this->source, $event->file())) {
            return;
        }

        $this->baseline->add(
            Issue::from(
                $this->relativePathFromBaseline($event->file()),
                $event->line(),
                $this->hash($event->file(), $event->line()),
                $event->message(),
            ),
        );
    }

    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function testTriggeredPhpDeprecation(PhpDeprecationTriggered $event): void
    {
        if (!$this->source->ignoreSuppressionOfPhpDeprecations() && $event->wasSuppressed()) {
            return;
        }

        if ($this->source->restrictDeprecations() && !(new SourceFilter)->includes($this->source, $event->file())) {
            return;
        }

        $this->baseline->add(
            Issue::from(
                $this->relativePathFromBaseline($event->file()),
                $event->line(),
                $this->hash($event->file(), $event->line()),
                $event->message(),
            ),
        );
    }

    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function testTriggeredNotice(NoticeTriggered $event): void
    {
        if (!$this->source->ignoreSuppressionOfNotices() && $event->wasSuppressed()) {
            return;
        }

        if ($this->source->restrictNotices() && !(new SourceFilter)->includes($this->source, $event->file())) {
            return;
        }

        $this->baseline->add(
            Issue::from(
                $this->relativePathFromBaseline($event->file()),
                $event->line(),
                $this->hash($event->file(), $event->line()),
                $event->message(),
            ),
        );
    }

    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function testTriggeredPhpNotice(PhpNoticeTriggered $event): void
    {
        if (!$this->source->ignoreSuppressionOfPhpNotices() && $event->wasSuppressed()) {
            return;
        }

        if ($this->source->restrictNotices() && !(new SourceFilter)->includes($this->source, $event->file())) {
            return;
        }

        $this->baseline->add(
            Issue::from(
                $this->relativePathFromBaseline($event->file()),
                $event->line(),
                $this->hash($event->file(), $event->line()),
                $event->message(),
            ),
        );
    }

    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function testTriggeredWarning(WarningTriggered $event): void
    {
        if (!$this->source->ignoreSuppressionOfWarnings() && $event->wasSuppressed()) {
            return;
        }

        if ($this->source->restrictWarnings() && !(new SourceFilter)->includes($this->source, $event->file())) {
            return;
        }

        $this->baseline->add(
            Issue::from(
                $this->relativePathFromBaseline($event->file()),
                $event->line(),
                $this->hash($event->file(), $event->line()),
                $event->message(),
            ),
        );
    }

    /**
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    public function testTriggeredPhpWarning(PhpWarningTriggered $event): void
    {
        if (!$this->source->ignoreSuppressionOfPhpWarnings() && $event->wasSuppressed()) {
            return;
        }

        if ($this->source->restrictWarnings() && !(new SourceFilter)->includes($this->source, $event->file())) {
            return;
        }

        $this->baseline->add(
            Issue::from(
                $this->relativePathFromBaseline($event->file()),
                $event->line(),
                $this->hash($event->file(), $event->line()),
                $event->message(),
            ),
        );
    }

    /**
     * @psalm-param non-empty-string $file
     *
     * @psalm-return non-empty-string
     *
     * @todo Actually implement this method
     */
    private function relativePathFromBaseline(string $file): string
    {
        return $file;
    }

    /**
     * @psalm-param non-empty-string $file
     * @psalm-param positive-int $line
     *
     * @psalm-return non-empty-string
     *
     * @throws FileDoesNotExistException
     * @throws FileDoesNotHaveLineException
     */
    private function hash(string $file, int $line): string
    {
        if (!is_file($file)) {
            throw new FileDoesNotExistException($file);
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $key   = $line - 1;

        if (!isset($lines[$key])) {
            throw new FileDoesNotHaveLineException($file, $line);
        }

        $hash = sha1($lines[$key]);

        assert(!empty($hash));

        return $hash;
    }
}
