<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Documentation;

/**
 * @internal
 */
final class CodeSample
{
    private string $code;
    private bool $reported;

    private function __construct(
        string $code,
        bool $reported
    ) {
        $this->code = $code;
        $this->reported = $reported;
    }

    public static function reported(string $code): self
    {
        return new self(
            $code,
            true,
        );
    }

    public static function notReported(string $code): self
    {
        return new self(
            $code,
            false,
        );
    }

    public function code(): string
    {
        return $this->code;
    }

    public function isReported(): bool
    {
        return $this->reported;
    }
}
