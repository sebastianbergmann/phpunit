<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use function count;

final class Stack
{
    /**
     * @var list<mixed>
     */
    private array $elements = [];

    public function push(mixed $element): void
    {
        $this->elements[] = $element;
    }

    public function size(): int
    {
        return count($this->elements);
    }
}
