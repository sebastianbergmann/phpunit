<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
 * @psalm-immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class TestWith
{
    private array $data;

    /**
     * @psalm-var ?non-empty-string
     */
    private ?string $name;

    /**
     * @psalm-param ?non-empty-string $name
     */
    public function __construct(array $data, ?string $name = null)
    {
        $this->data = $data;
        $this->name = $name;
    }

    public function data(): array
    {
        return $this->data;
    }

    /**
     * @psalm-return ?non-empty-string
     */
    public function name(): ?string
    {
        return $this->name;
    }
}
