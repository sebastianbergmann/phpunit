<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataProviderInvalidKeyTest extends TestCase
{
    public static function provider(): Iterator
    {
        return new class implements Iterator
        {
            private int $position;

            public function current(): string
            {
                return 'value';
            }

            public function next(): void
            {
                $this->position++;
            }

            public function key(): float
            {
                return 0.1;
            }

            public function valid(): bool
            {
                return $this->position === 0;
            }

            public function rewind(): void
            {
                $this->position = 0;
            }
        };
    }

    #[DataProvider('provider')]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
