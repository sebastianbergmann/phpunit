<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestData;

use function count;
use Countable;
use IteratorAggregate;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class SerializedValueCollection implements Countable, IteratorAggregate
{
    /**
     * @psalm-var list<SerializedValue>
     */
    private readonly array $values;

    public static function from(mixed $values): self
    {
        $data = [];

        foreach ($values as $value) {
            $data[] = SerializedValue::from($value);
        }

        return new self(...$data);
    }

    private function __construct(SerializedValue ...$data)
    {
        $this->values = $data;
    }

    /**
     * @psalm-return list<SerializedValue>
     */
    public function asArray(): array
    {
        return $this->values;
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function getIterator(): SerializedValueCollectionIterator
    {
        return new SerializedValueCollectionIterator($this);
    }
}
