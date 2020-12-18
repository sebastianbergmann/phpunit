<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test\Result;

use PHPUnit\Event\Test\Result;

final class Success implements Result
{
    private int $numberOfAssertions;

    public function __construct(int $numberOfAssertions)
    {
        $this->numberOfAssertions = $numberOfAssertions;
    }

    public function is(Result $other): bool
    {
        return $other->asString() === $this->asString();
    }

    public function asString(): string
    {
        return 'success';
    }

    public function numberOfAssertions(): int
    {
        return $this->numberOfAssertions;
    }
}
