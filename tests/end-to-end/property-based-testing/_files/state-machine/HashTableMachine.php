<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\StateMachine;

use function count;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\FromPool;
use PHPUnit\Framework\Attributes\StateMachineRule;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

final class HashTableMachine
{
    private HashTable $table;

    /**
     * @var array<int, int>
     */
    private array $model = [];

    /**
     * @return ValueGenerator<int>
     */
    public static function keys(): ValueGenerator
    {
        return Generators::integers(0, 40);
    }

    public function __construct()
    {
        $this->table = new HashTable;
    }

    #[StateMachineRule(produces: 'keys', precondition: 'hasFreeSlots')]
    public function put(#[ForAll('keys')] int $key, int $value): int
    {
        $this->table->put($key, $value);

        $this->model[$key] = $value;

        return $key;
    }

    #[StateMachineRule]
    public function get(#[FromPool('keys')] int $key): ?int
    {
        $expected = null;

        if (isset($this->model[$key])) {
            $expected = $this->model[$key];
        }

        $actual = $this->table->get($key);

        Assert::assertSame($expected, $actual);

        return $actual;
    }

    #[StateMachineRule]
    public function delete(#[FromPool('keys', consume: true)] int $key): void
    {
        $this->table->delete($key);

        unset($this->model[$key]);
    }

    public function hasFreeSlots(): bool
    {
        return count($this->model) < 6;
    }
}
