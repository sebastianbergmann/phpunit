<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Sorted implements Event
{
    private Telemetry\Info $telemetryInfo;
    private int $executionOrder;
    private int $executionOrderDefects;
    private bool $resolveDependencies;

    /**
     * @var list<non-empty-string>
     */
    private array $pipeline;

    /**
     * @param list<non-empty-string> $pipeline
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(Telemetry\Info $telemetryInfo, int $executionOrder, int $executionOrderDefects, bool $resolveDependencies, array $pipeline)
    {
        $this->telemetryInfo         = $telemetryInfo;
        $this->executionOrder        = $executionOrder;
        $this->executionOrderDefects = $executionOrderDefects;
        $this->resolveDependencies   = $resolveDependencies;
        $this->pipeline              = $pipeline;
    }

    public function telemetryInfo(): Telemetry\Info
    {
        return $this->telemetryInfo;
    }

    public function executionOrder(): int
    {
        return $this->executionOrder;
    }

    public function executionOrderDefects(): int
    {
        return $this->executionOrderDefects;
    }

    public function resolveDependencies(): bool
    {
        return $this->resolveDependencies;
    }

    /**
     * The stages of the reordering pipeline that was applied, in the order in
     * which they were applied.
     *
     * @return list<non-empty-string>
     */
    public function pipeline(): array
    {
        return $this->pipeline;
    }

    /**
     * @return non-empty-string
     */
    public function asString(): string
    {
        return 'Test Suite Sorted';
    }
}
