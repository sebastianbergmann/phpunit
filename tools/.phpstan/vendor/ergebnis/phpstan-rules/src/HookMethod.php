<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules;

/**
 * @internal
 */
final class HookMethod
{
    private ClassName $className;
    private MethodName $methodName;
    private Invocation $invocation;
    private HasContent $hasContent;

    private function __construct(
        ClassName $className,
        MethodName $methodName,
        Invocation $invocation,
        HasContent $hasContent
    ) {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->invocation = $invocation;
        $this->hasContent = $hasContent;
    }

    public static function create(
        ClassName $className,
        MethodName $methodName,
        Invocation $invocation,
        HasContent $hasContent
    ): self {
        return new self(
            $className,
            $methodName,
            $invocation,
            $hasContent,
        );
    }

    public function className(): ClassName
    {
        return $this->className;
    }

    public function methodName(): MethodName
    {
        return $this->methodName;
    }

    public function invocation(): Invocation
    {
        return $this->invocation;
    }

    public function hasContent(): HasContent
    {
        return $this->hasContent;
    }
}
