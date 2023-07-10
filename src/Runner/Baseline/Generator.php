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
            Issue::fromFileAndLine(
                $event->file(),
                $event->line(),
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
            Issue::fromFileAndLine(
                $event->file(),
                $event->line(),
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
            Issue::fromFileAndLine(
                $event->file(),
                $event->line(),
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
            Issue::fromFileAndLine(
                $event->file(),
                $event->line(),
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
            Issue::fromFileAndLine(
                $event->file(),
                $event->line(),
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
            Issue::fromFileAndLine(
                $event->file(),
                $event->line(),
                $event->message(),
            ),
        );
    }
}
