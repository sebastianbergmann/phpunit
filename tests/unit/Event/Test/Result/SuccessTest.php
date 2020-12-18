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

/**
 * @covers \PHPUnit\Event\Test\Result\Success
 */
final class SuccessTest extends AbstractResultTestCase
{
    public function testConstructorSetsValues(): void
    {
        $numberOfAssertions = 3;

        $result = new Success($numberOfAssertions);

        $this->assertSame($numberOfAssertions, $result->numberOfAssertions());
    }

    protected function asString(): string
    {
        return 'success';
    }

    protected function result(): Result
    {
        return new Success(3);
    }
}
