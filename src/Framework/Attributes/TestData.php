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
final class TestData
{
    private readonly array $data;
    private readonly ?string $name;

    public function __construct(mixed ...$data)
    {
        $this->name = isset($data['name']) ? (string) $data['name'] : null;
        unset($data['name']);
        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function name(): ?string
    {
        return $this->name;
    }
}
