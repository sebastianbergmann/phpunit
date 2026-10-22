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

use PHPUnit\Framework\TestCase;

interface DeprecatedIdAndAfterForMockObjectExpectationsInterface
{
    public function one(): void;

    public function two(): void;
}

final class DeprecatedIdAndAfterForMockObjectExpectationsTest extends TestCase
{
    public function testIdAndAfter(): void
    {
        $mock = $this->createMock(DeprecatedIdAndAfterForMockObjectExpectationsInterface::class);

        $mock
            ->expects($this->once())
            ->method('one')
            ->id('one');

        $mock
            ->expects($this->once())
            ->method('two')
            ->after('one');

        $mock->one();
        $mock->two();
    }
}
