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

use Generator;
use IteratorAggregate;

final class WrapperIteratorAggregate implements IteratorAggregate
{
    private iterable $baseCollection;

    public function __construct(iterable $baseCollection)
    {
        $this->baseCollection = $baseCollection;
    }

    public function getIterator(): Generator
    {
        foreach ($this->baseCollection as $k => $v) {
            yield $k => $v;
        }
    }
}
