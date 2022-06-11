<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use PHPUnit\Framework\Constraint;
use SebastianBergmann\Exporter\Exporter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class AssertionMade implements Event
{
    private Telemetry\Info $telemetryInfo;
    private mixed $value;
    private Constraint\Constraint $constraint;
    private string $message;
    private bool $hasFailed;

    public function __construct(Telemetry\Info $telemetryInfo, mixed $value, Constraint\Constraint $constraint, string $message, bool $hasFailed)
    {
        $this->telemetryInfo = $telemetryInfo;
        $this->value         = $value;
        $this->constraint    = $constraint;
        $this->message       = $message;
        $this->hasFailed     = $hasFailed;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function count(): int
    {
        return $this->constraint->count();
    }

    public function message(): string
    {
        return $this->message;
    }

    public function hasFailed(): bool
    {
        return $this->hasFailed;
    }

    public function asString(): string
    {
        $message = '';

        if (!empty($this->message)) {
            $message = sprintf(
                ', Message: %s',
                $this->message
            );
        }

        $status = 'Succeeded';

        if ($this->hasFailed) {
            $status = 'Failed';
        }

        return sprintf(
            'Assertion %s (Constraint: %s, Value: %s%s)',
            $status,
            $this->constraint->toString(),
            $this->valueAsString(),
            $message
        );
    }

    private function valueAsString(): string
    {
        return (new Exporter)->export($this->value());
    }
}
