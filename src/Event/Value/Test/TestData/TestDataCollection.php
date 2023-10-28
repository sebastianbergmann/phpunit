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
 * @template-implements IteratorAggregate<int, TestData>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class TestDataCollection implements Countable, IteratorAggregate
{
    /**
     * @psalm-var list<TestData>
     */
    private readonly array $data;
    private ?DataFromDataProvider $fromDataProvider = null;

    /**
     * @psalm-param list<TestData> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }

    private function __construct(TestData ...$data)
    {
        foreach ($data as $_data) {
            if ($_data->isFromDataProvider()) {
                $this->fromDataProvider = $_data;
            }
        }

        $this->data = $data;
    }

    /**
     * @psalm-return list<TestData>
     */
    public function asArray(): array
    {
        return $this->data;
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @psalm-assert-if-true !null $this->fromDataProvider
     */
    public function hasDataFromDataProvider(): bool
    {
        return $this->fromDataProvider !== null;
    }

    /**
     * @throws NoDataSetFromDataProviderException
     */
    public function dataFromDataProvider(): DataFromDataProvider
    {
        if (!$this->hasDataFromDataProvider()) {
            throw new NoDataSetFromDataProviderException;
        }

        return $this->fromDataProvider;
    }

    public function getIterator(): TestDataCollectionIterator
    {
        return new TestDataCollectionIterator($this);
    }
}
