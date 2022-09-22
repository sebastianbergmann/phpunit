<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class RequiresMethod extends Metadata
{
    /**
     * @psalm-var class-string
     */
    private readonly string $className;
    private readonly string $methodName;

    /**
     * @psalm-param class-string $className
     */
    protected function __construct(int $level, string $className, string $methodName)
    {
        parent::__construct($level);

        $this->className  = $className;
        $this->methodName = $methodName;
    }

    public function isrequiresMethod(): bool
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
}
