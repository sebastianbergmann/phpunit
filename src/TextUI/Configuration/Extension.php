<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestListener;
use PHPUnit\Runner\Hook;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Extension
{
    /**
     * @var string
     * @psalm-var class-string
     */
    private $className;

    /**
     * @var string
     */
    private $sourceFile;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className, string $sourceFile, array $arguments)
    {
        $this->className  = $className;
        $this->sourceFile = $sourceFile;
        $this->arguments  = $arguments;
    }

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

    public function createHookInstance(): Hook
    {
        $object = $this->createInstance();

        if (!$object instanceof Hook) {
            throw new Exception(
                \sprintf(
                    'Class "%s" does not implement a PHPUnit\Runner\Hook interface',
                    $this->className
                )
            );
        }

        return $object;
    }

    public function createTestListenerInstance(): TestListener
    {
        $object = $this->createInstance();

        if (!$object instanceof TestListener) {
            throw new Exception(
                \sprintf(
                    'Class "%s" does not implement the PHPUnit\Framework\TestListener interface',
                    $this->className
                )
            );
        }

        return $object;
    }

    private function createInstance(): object
    {
        $this->ensureClassExists();

        try {
            $reflector = new \ReflectionClass($this->className);
        } catch (\ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        if (!$this->hasArguments()) {
            return $reflector->newInstance();
        }

        return $reflector->newInstanceArgs($this->arguments);
    }

    /**
     * @throws Exception
     */
    private function ensureClassExists(): void
    {
        if (\class_exists($this->className, false)) {
            return;
        }

        if ($this->hasSourceFile()) {
            /** @noinspection PhpIncludeInspection */
            require_once $this->sourceFile;
        }

        if (!\class_exists($this->className)) {
            throw new Exception(
                \sprintf(
                    'Class "%s" does not exist',
                    $this->className
                )
            );
        }
    }
}
