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

use IteratorAggregate;
use ReturnTypeWillChange;
use Traversable;

class TestIteratorAggregate2 implements IteratorAggregate
{
    private $traversable;

    public function __construct(Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return $this->traversable;
    }
}
