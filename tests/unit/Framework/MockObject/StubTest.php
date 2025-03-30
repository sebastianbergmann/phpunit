<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;

#[Group('test-doubles')]
#[Group('test-doubles/test-stub')]
#[Medium]
#[TestDox('Test Stub')]
final class StubTest extends TestDoubleTestCase
{
    /**
     * @param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createStub($type);
    }
}
