<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6408;

use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Traversable;

final class Issue6476Test extends TestCase
{
    public function testIteratorAggregate(): void
    {
        $this->assertCount(
            0,
            new class implements IteratorAggregate
            {
                public function __construct()
                {
                }

                public function getIterator(): Traversable
                {
                    return $this;
                }
            },
        );
    }
}
