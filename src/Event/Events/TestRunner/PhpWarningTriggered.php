<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestRunner;

use const PHP_EOL;
use function implode;
use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PhpWarningTriggered implements Event
{
    private Telemetry\Info $telemetryInfo;

    /**
     * @var non-empty-string
     */
    private string $message;

    /**
     * @var non-empty-string
     */
    private string $file;

    /**
     * @var positive-int
     */
    private int $line;
    private bool $suppressed;
    private bool $ignoredByBaseline;

    /**
     * @param non-empty-string $message
     * @param non-empty-string $file
     * @param positive-int     $line
     */
    public function __construct(Telemetry\Info $telemetryInfo, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline)
    {
        $this->telemetryInfo     = $telemetryInfo;
        $this->message           = $message;
        $this->file              = $file;
        $this->line              = $line;
        $this->suppressed        = $suppressed;
        $this->ignoredByBaseline = $ignoredByBaseline;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    /**
     * @return non-empty-string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return non-empty-string
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * @return positive-int
     */
    public function line(): int
    {
        return $this->line;
    }

    public function wasSuppressed(): bool
    {
        return $this->suppressed;
    }

    public function ignoredByBaseline(): bool
    {
        return $this->ignoredByBaseline;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        $message = $this->message;

        if ($message !== '') {
            $message = PHP_EOL . $message;
        }

        $details = [];

        if ($this->suppressed) {
            $details[] = 'suppressed using operator';
        }

        if ($this->ignoredByBaseline) {
            $details[] = 'ignored by baseline';
        }

        return sprintf(
            'Test Runner Triggered PHP Warning (%s) in %s:%d%s',
            $details !== [] ? implode(', ', $details) : '',
            $this->file,
            $this->line,
            $message,
        );
    }
}
