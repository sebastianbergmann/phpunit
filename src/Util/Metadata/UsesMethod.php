<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class UsesMethod extends Metadata
{
    /**
     * @psalm-var class-string
     */
    private string $className;

    private string $methodName;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className, string $methodName)
    {
        $this->className  = $className;
        $this->methodName = $methodName;
    }

    public function isUsesMethod(): bool
    {
        return true;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function asString(): string
    {
        return $this->className . '::' . $this->methodName;
    }
}
