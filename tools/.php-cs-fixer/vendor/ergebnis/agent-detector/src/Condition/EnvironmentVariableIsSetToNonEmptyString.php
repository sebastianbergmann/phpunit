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
final class EnvironmentVariableIsSetToNonEmptyString implements Condition
{
    private string $name;
    private string $source;

    private function __construct(
        string $name,
        string $source
    ) {
        $this->name = $name;
        $this->source = $source;
    }

    public static function create(
        string $name,
        string $source
    ): self {
        return new self(
            $name,
            $source,
        );
    }

    public function isMet(): bool
    {
        $value = \getenv($this->name);

        if (!\is_string($value)) {
            return false;
        }

        return '' !== $value;
    }

    public function source(): string
    {
        return $this->source;
    }
}
