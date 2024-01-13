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
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestWith extends Metadata
{
    private array $data;

    /**
     * @psalm-var ?non-empty-string
     */
    private ?string $name;

    /**
     * @psalm-param 0|1 $level
     * @psalm-param ?non-empty-string $name
     */
    protected function __construct(int $level, array $data, ?string $name = null)
    {
        parent::__construct($level);

        $this->data = $data;
        $this->name = $name;
    }

    /**
     * @psalm-assert-if-true TestWith $this
     */
    public function isTestWith(): bool
    {
        return true;
    }

    public function data(): array
    {
        return $this->data;
    }

    /**
     * @psalm-assert-if-true !null $this->name
     */
    public function hasName(): bool
    {
        return $this->name !== null;
    }

    /**
     * @psalm-return ?non-empty-string
     */
    public function name(): ?string
    {
        return $this->name;
    }
}
