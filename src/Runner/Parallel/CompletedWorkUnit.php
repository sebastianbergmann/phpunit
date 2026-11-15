<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

/**
 * The outcome of a WorkUnit that a worker has finished: the serialized result
 * envelope written by the worker, together with the unit it belongs to and the
 * nonce that authenticates the envelope.
 *
 * A unit whose worker died before reporting completion is marked as crashed;
 * the serialized envelope is then empty. The two outcomes are constructed
 * through the named constructors fromEnvelope() and fromCrash(), which encode
 * that a finished unit always carries an envelope and its nonce while a
 * crashed unit carries neither.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CompletedWorkUnit
{
    private WorkUnit $unit;
    private string $serializedResult;

    /**
     * @var ?non-empty-string
     */
    private ?string $nonce;
    private bool $crashed;

    /**
     * A human-readable explanation of why a crashed unit did not run, used in
     * place of the generic "ended unexpectedly" message when a more specific
     * reason is known (for example, that the unit could not be dispatched).
     */
    private ?string $message;

    /**
     * What the worker process wrote while it was running the unit, and that
     * was not handed over with the events that the unit streamed. For a unit
     * that finished, this is what was written to the standard output after
     * its last test had finished; for a unit whose worker died, it is what
     * was written to the standard output and to the standard error, which
     * may tell why the worker died.
     */
    private string $output;

    /**
     * What the worker process wrote to its standard error after the last
     * test of a unit that finished had finished.
     */
    private string $errorOutput;

    /**
     * Whether the unit was abandoned while it was executing because the time
     * limit for the test run was exceeded. The message is then the one that
     * the test that was running is reported as aborted with.
     */
    private bool $abortedByTimeLimit;

    /**
     * The exit code of the worker process that died while it was running
     * the unit, when the worker process ended on its own and it is known how.
     */
    private ?int $exitCode;

    /**
     * The signal that terminated the worker process that died while it was
     * running the unit, when a signal terminated it.
     */
    private ?int $signal;

    /**
     * @param non-empty-string $nonce
     */
    public static function fromEnvelope(WorkUnit $unit, string $serializedResult, string $nonce, string $output = '', string $errorOutput = ''): self
    {
        return new self($unit, $serializedResult, $nonce, false, null, $output, $errorOutput, false, null, null);
    }

    public static function fromCrash(WorkUnit $unit, ?string $message = null, string $output = '', ?int $exitCode = null, ?int $signal = null): self
    {
        return new self($unit, '', null, true, $message, $output, '', false, $exitCode, $signal);
    }

    /**
     * @param non-empty-string $message
     */
    public static function fromAbortionByTimeLimit(WorkUnit $unit, string $message): self
    {
        return new self($unit, '', null, false, $message, '', '', true, null, null);
    }

    /**
     * @param ?non-empty-string $nonce
     */
    private function __construct(WorkUnit $unit, string $serializedResult, ?string $nonce, bool $crashed, ?string $message, string $output, string $errorOutput, bool $abortedByTimeLimit, ?int $exitCode, ?int $signal)
    {
        $this->unit               = $unit;
        $this->serializedResult   = $serializedResult;
        $this->nonce              = $nonce;
        $this->crashed            = $crashed;
        $this->message            = $message;
        $this->output             = $output;
        $this->errorOutput        = $errorOutput;
        $this->abortedByTimeLimit = $abortedByTimeLimit;
        $this->exitCode           = $exitCode;
        $this->signal             = $signal;
    }

    public function abortedByTimeLimit(): bool
    {
        return $this->abortedByTimeLimit;
    }

    public function unit(): WorkUnit
    {
        return $this->unit;
    }

    public function serializedResult(): string
    {
        return $this->serializedResult;
    }

    /**
     * @return ?non-empty-string
     */
    public function nonce(): ?string
    {
        return $this->nonce;
    }

    public function crashed(): bool
    {
        return $this->crashed;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function output(): string
    {
        return $this->output;
    }

    public function errorOutput(): string
    {
        return $this->errorOutput;
    }

    public function exitCode(): ?int
    {
        return $this->exitCode;
    }

    public function signal(): ?int
    {
        return $this->signal;
    }
}
