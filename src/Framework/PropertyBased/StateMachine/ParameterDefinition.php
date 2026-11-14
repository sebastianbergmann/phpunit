<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\StateMachine;

use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;

/**
 * A parameter of a step of a state machine: it receives a value from a pool
 * (#[FromPool]), or a value of a generator, which is the one that is specified
 * with #[ForAll] or #[ForAllExternal], or the one that is derived from its
 * type.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ParameterDefinition
{
    /**
     * @var non-empty-string
     */
    private string $name;

    /**
     * @var ?non-empty-string
     */
    private ?string $pool;
    private bool $consume;

    /**
     * @var ?AbstractGenerator<mixed>
     */
    private ?AbstractGenerator $generator;

    /**
     * @param non-empty-string $name
     * @param non-empty-string $pool
     */
    public static function fromPool(string $name, string $pool, bool $consume): self
    {
        return new self($name, $pool, $consume, null);
    }

    /**
     * @param non-empty-string         $name
     * @param AbstractGenerator<mixed> $generator
     */
    public static function generated(string $name, AbstractGenerator $generator): self
    {
        return new self($name, null, false, $generator);
    }

    /**
     * @param non-empty-string          $name
     * @param ?non-empty-string         $pool
     * @param ?AbstractGenerator<mixed> $generator
     */
    private function __construct(string $name, ?string $pool, bool $consume, ?AbstractGenerator $generator)
    {
        $this->name      = $name;
        $this->pool      = $pool;
        $this->consume   = $consume;
        $this->generator = $generator;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * The pool from which the parameter receives a value, null when it
     * receives a generated value.
     *
     * @return ?non-empty-string
     */
    public function pool(): ?string
    {
        return $this->pool;
    }

    /**
     * Whether the value that the parameter receives is removed from its pool.
     */
    public function consume(): bool
    {
        return $this->consume;
    }

    /**
     * The generator of the values that the parameter receives, null when it
     * receives a value from a pool.
     *
     * @return ?AbstractGenerator<mixed>
     */
    public function generator(): ?AbstractGenerator
    {
        return $this->generator;
    }
}
