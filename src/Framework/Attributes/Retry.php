<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Retry
{
    private int $maxRetries;
    private int $delay;

    /**
     * @var ?non-empty-string
     */
    private ?string $retryOn;

    /**
     * @param ?non-empty-string $retryOn
     */
    public function __construct(int $maxRetries, ?int $delay = 0, ?string $retryOn = null)
    {
        $this->maxRetries = $maxRetries;
        $this->delay      = $delay;
        $this->retryOn    = $retryOn;
    }

    public function maxRetries(): int
    {
        return $this->maxRetries;
    }

    public function delay(): int
    {
        return $this->delay;
    }

    /**
     * @return ?non-empty-string
     */
    public function retryOn(): ?string
    {
        return $this->retryOn;
    }
}
