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

use function array_fill;

/**
 * A hash table with open addressing: a key that hashes to a slot that is
 * taken is stored in the next free slot.
 */
final class HashTable
{
    private const int NUMBER_OF_SLOTS = 8;

    /**
     * @var list<?array{0: int, 1: int}>
     */
    private array $slots;

    public function __construct()
    {
        $this->slots = array_fill(0, self::NUMBER_OF_SLOTS, null);
    }

    public function put(int $key, int $value): void
    {
        for ($probe = 0; $probe < self::NUMBER_OF_SLOTS; $probe++) {
            $slot = ($key + $probe) % self::NUMBER_OF_SLOTS;

            if ($this->slots[$slot] === null || $this->slots[$slot][0] === $key) {
                $this->slots[$slot] = [$key, $value];

                return;
            }
        }
    }

    public function get(int $key): ?int
    {
        for ($probe = 0; $probe < self::NUMBER_OF_SLOTS; $probe++) {
            $slot = ($key + $probe) % self::NUMBER_OF_SLOTS;

            if ($this->slots[$slot] === null) {
                return null;
            }

            if ($this->slots[$slot][0] === $key) {
                return $this->slots[$slot][1];
            }
        }

        return null;
    }

    public function delete(int $key): void
    {
        for ($probe = 0; $probe < self::NUMBER_OF_SLOTS; $probe++) {
            $slot = ($key + $probe) % self::NUMBER_OF_SLOTS;

            if ($this->slots[$slot] === null) {
                return;
            }

            if ($this->slots[$slot][0] === $key) {
                // the defect: the keys that were stored after this slot
                // because it was taken are no longer found
                $this->slots[$slot] = null;

                return;
            }
        }
    }
}
