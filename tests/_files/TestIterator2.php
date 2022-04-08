<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use function current;
use function key;
use function next;
use function reset;
use Iterator;
use ReturnTypeWillChange;

class TestIterator2 implements Iterator
{
    protected $data;

    public function __construct(array $array)
    {
        $this->data = $array;
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return current($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return key($this->data);
    }

    #[ReturnTypeWillChange]
    public function valid()
    {
        return key($this->data) !== null;
    }

    public function rewind(): void
    {
        reset($this->data);
    }
}
