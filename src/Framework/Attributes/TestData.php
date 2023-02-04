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

use function is_string;
use Attribute;
use PHPUnit\Event\InvalidArgumentException;

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

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(mixed ...$data)
    {
        if (isset($data['name'])) {
            if (!is_string($data['name'])) {
                throw new InvalidArgumentException('Name must be of type string.');
            }

            $this->name = $data['name'];
            unset($data['name']);
        } else {
            $this->name = null;
        }

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
