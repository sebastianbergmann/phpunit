<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Extension
{
    /**
     * @psalm-var class-string
     */
    private string $className;
    private string $sourceFile;
    private array $arguments;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className, string $sourceFile, array $arguments)
    {
        $this->className  = $className;
        $this->sourceFile = $sourceFile;
        $this->arguments  = $arguments;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function hasSourceFile(): bool
    {
        return $this->sourceFile !== '';
    }

    public function sourceFile(): string
    {
        return $this->sourceFile;
    }

    public function hasArguments(): bool
    {
        return !empty($this->arguments);
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
