<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Tells which of the issues that an example triggered falsify its property.
 *
 * An issue falsifies the property when the test run fails because of it: an
 * E_USER_ERROR always, and a deprecation, a notice, or a warning when the
 * test runner is configured to fail on it. The test run fails because of the
 * issue no matter which example triggers it, but only an example that
 * falsifies the property is replayed and shrunk, so that the property is
 * reported with the simplest arguments for which the issue is triggered.
 *
 * An issue that is not reported, because it is suppressed, ignored by the
 * test, ignored by the baseline, or not triggered in the code that issues are
 * restricted to, does not make the test run fail and does not falsify the
 * property.
 *
 * The issues that PHPUnit triggers for a test, a test that is considered
 * risky, for instance, concern the property rather than one of its examples
 * and do not falsify the property either.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FalsifyingIssues
{
    private IssueFilter $filter;
    private bool $selfDeprecations;
    private bool $directDeprecations;
    private bool $indirectDeprecations;
    private bool $deprecationsWithUnknownTrigger;
    private bool $notices;
    private bool $warnings;

    /**
     * The issues for which the test run fails are the ones for which
     * PHPUnit\TextUI\ShellExitCodeCalculator signals failure.
     */
    public static function fromConfiguration(Configuration $configuration): self
    {
        $deprecations = $configuration->failOnAllIssues();
        $notices      = $configuration->failOnAllIssues();
        $warnings     = $configuration->failOnAllIssues();

        if ($configuration->failOnDeprecation()) {
            $deprecations = true;
        }

        if ($configuration->doNotFailOnDeprecation()) {
            $deprecations = false;
        }

        $selfDeprecations     = $deprecations;
        $directDeprecations   = $deprecations;
        $indirectDeprecations = $deprecations;

        if ($configuration->failOnSelfDeprecation()) {
            $selfDeprecations = true;
        }

        if ($configuration->doNotFailOnSelfDeprecation()) {
            $selfDeprecations = false;
        }

        if ($configuration->failOnDirectDeprecation()) {
            $directDeprecations = true;
        }

        if ($configuration->doNotFailOnDirectDeprecation()) {
            $directDeprecations = false;
        }

        if ($configuration->failOnIndirectDeprecation()) {
            $indirectDeprecations = true;
        }

        if ($configuration->doNotFailOnIndirectDeprecation()) {
            $indirectDeprecations = false;
        }

        if ($configuration->failOnNotice()) {
            $notices = true;
        }

        if ($configuration->doNotFailOnNotice()) {
            $notices = false;
        }

        if ($configuration->failOnWarning()) {
            $warnings = true;
        }

        if ($configuration->doNotFailOnWarning()) {
            $warnings = false;
        }

        return new self(
            new IssueFilter($configuration->source()),
            $selfDeprecations,
            $directDeprecations,
            $indirectDeprecations,
            $deprecations,
            $notices,
            $warnings,
        );
    }

    /**
     * @param bool $selfDeprecations               whether the test run fails for a deprecation that was triggered by first-party code for itself
     * @param bool $directDeprecations             whether the test run fails for a deprecation that was triggered by first-party code calling third-party code
     * @param bool $indirectDeprecations           whether the test run fails for a deprecation that was triggered by third-party code
     * @param bool $deprecationsWithUnknownTrigger whether the test run fails for a deprecation of which it is not known what triggered it
     */
    public function __construct(IssueFilter $filter, bool $selfDeprecations, bool $directDeprecations, bool $indirectDeprecations, bool $deprecationsWithUnknownTrigger, bool $notices, bool $warnings)
    {
        $this->filter                         = $filter;
        $this->selfDeprecations               = $selfDeprecations;
        $this->directDeprecations             = $directDeprecations;
        $this->indirectDeprecations           = $indirectDeprecations;
        $this->deprecationsWithUnknownTrigger = $deprecationsWithUnknownTrigger;
        $this->notices                        = $notices;
        $this->warnings                       = $warnings;
    }

    /**
     * How the first issue among the events of an example that falsifies the
     * property does so, null when no issue does.
     */
    public function falsificationBy(EventCollection $exampleEvents): ?Falsification
    {
        foreach ($exampleEvents as $event) {
            if ($event instanceof ErrorTriggered) {
                if ($this->filter->shouldBeProcessed($event)) {
                    return Falsification::ofIssue('E_USER_ERROR', $event->file(), $event->line(), $event->message());
                }

                continue;
            }

            if ($event instanceof PhpWarningTriggered) {
                if ($this->warnings && $this->isReported($event)) {
                    return Falsification::ofIssue('E_WARNING', $event->file(), $event->line(), $event->message());
                }

                continue;
            }

            if ($event instanceof WarningTriggered) {
                if ($this->warnings && $this->isReported($event)) {
                    return Falsification::ofIssue('E_USER_WARNING', $event->file(), $event->line(), $event->message());
                }

                continue;
            }

            if ($event instanceof PhpNoticeTriggered) {
                if ($this->notices && $this->isReported($event)) {
                    return Falsification::ofIssue('E_NOTICE', $event->file(), $event->line(), $event->message());
                }

                continue;
            }

            if ($event instanceof NoticeTriggered) {
                if ($this->notices && $this->isReported($event)) {
                    return Falsification::ofIssue('E_USER_NOTICE', $event->file(), $event->line(), $event->message());
                }

                continue;
            }

            if ($event instanceof PhpDeprecationTriggered) {
                if ($this->failsForDeprecation($event) && $this->isReported($event)) {
                    return Falsification::ofIssue('E_DEPRECATED', $event->file(), $event->line(), $event->message());
                }

                continue;
            }

            if ($event instanceof DeprecationTriggered) {
                if ($this->failsForDeprecation($event) && $this->isReported($event)) {
                    return Falsification::ofIssue('E_USER_DEPRECATED', $event->file(), $event->line(), $event->message());
                }
            }
        }

        return null;
    }

    private function isReported(DeprecationTriggered|NoticeTriggered|PhpDeprecationTriggered|PhpNoticeTriggered|PhpWarningTriggered|WarningTriggered $event): bool
    {
        return $this->filter->shouldBeProcessed($event) && !$event->ignoredByBaseline();
    }

    private function failsForDeprecation(DeprecationTriggered|PhpDeprecationTriggered $event): bool
    {
        if ($event->trigger()->isSelf()) {
            return $this->selfDeprecations;
        }

        if ($event->trigger()->isDirect()) {
            return $this->directDeprecations;
        }

        if ($event->trigger()->isIndirect()) {
            return $this->indirectDeprecations;
        }

        return $this->deprecationsWithUnknownTrigger;
    }
}
