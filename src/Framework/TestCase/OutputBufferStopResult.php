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

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class OutputBufferStopResult
{
    public bool $closedCleanly;

    /**
     * @var null|non-empty-string
     */
    public ?string $riskyMessage;

    /**
     * @param null|non-empty-string $riskyMessage
     */
    public function __construct(bool $closedCleanly, ?string $riskyMessage)
    {
        $this->closedCleanly = $closedCleanly;
        $this->riskyMessage  = $riskyMessage;
    }
}
