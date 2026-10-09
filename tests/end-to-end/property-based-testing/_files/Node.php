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

final readonly class Node
{
    public int $value;
    public ?self $next;

    public function __construct(int $value, ?self $next)
    {
        $this->value = $value;
        $this->next  = $next;
    }

    public function length(): int
    {
        $length = 1;
        $node   = $this->next;

        while ($node !== null) {
            $length++;

            $node = $node->next;
        }

        return $length;
    }
}
