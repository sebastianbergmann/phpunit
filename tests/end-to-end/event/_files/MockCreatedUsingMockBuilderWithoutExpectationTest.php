<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace TestFixture\PHPUnit\Event;

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Event\Example;

final class MockCreatedUsingMockBuilderWithoutExpectationTest extends TestCase
{
    public function testSuccess(): void
    {
        $this->getMockBuilder(Example::class)->getMock();

        $this->assertTrue(true);
    }
}
