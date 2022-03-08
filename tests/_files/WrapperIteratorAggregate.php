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

use function assert;
use function is_array;
use IteratorAggregate;
use ReturnTypeWillChange;
use Traversable;

class WrapperIteratorAggregate implements IteratorAggregate
{
    /**
     * @var array|Traversable
     */
    private $baseCollection;

    public function __construct($baseCollection)
    {
        assert(is_array($baseCollection) || $baseCollection instanceof Traversable);
        $this->baseCollection = $baseCollection;
    }

    #[ReturnTypeWillChange]
    public function getIterator()
    {
        foreach ($this->baseCollection as $k => $v) {
            yield $k => $v;
        }
    }
}
