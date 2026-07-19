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
     * reason is known (for example, that the unit's test data could not be
     * serialized for transport to a worker).
     */
    private ?string $message;

    /**
     * @param non-empty-string $nonce
     */
    public static function fromEnvelope(WorkUnit $unit, string $serializedResult, string $nonce): self
    {
        return new self($unit, $serializedResult, $nonce, false, null);
    }

    public static function fromCrash(WorkUnit $unit, ?string $message = null): self
    {
        return new self($unit, '', null, true, $message);
    }

    /**
     * @param ?non-empty-string $nonce
     */
    private function __construct(WorkUnit $unit, string $serializedResult, ?string $nonce, bool $crashed, ?string $message)
    {
        $this->unit             = $unit;
        $this->serializedResult = $serializedResult;
        $this->nonce            = $nonce;
        $this->crashed          = $crashed;
        $this->message          = $message;
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
}
