<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

class SameSize extends Count
{
    /**
     * @var int
     */
    protected $expectedCount;

    /**
     * @param \Countable|\Traversable|array $expected
     */
    public function __construct($expected)
    {
        parent::__construct($this->getCountOf($expected));
    }
}
