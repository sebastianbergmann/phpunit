<?php

declare(strict_types=1);

/**
 * Copyright (c) 2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/agent-detector
 */

namespace Ergebnis\AgentDetector\Condition;

/**
 * @internal
 */
final class EnvironmentVariableIsSetTo implements Condition
{
    private string $name;
    private string $value;
    private string $source;

    private function __construct(
        string $name,
        string $value,
        string $source
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->source = $source;
    }

    public static function create(
        string $name,
        string $value,
        string $source
    ): self {
        return new self(
            $name,
            $value,
            $source,
        );
    }

    public function isMet(): bool
    {
        return \getenv($this->name) === $this->value;
    }

    public function source(): string
    {
        return $this->source;
    }
}
