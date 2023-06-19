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

use PHPUnit\Framework\Attributes\Small;
use unit\Framework\MockObject\TestDoubleTestCase;

#[Small]
final class StubTest extends TestDoubleTestCase
{
    /**
     * @psalm-param class-string $type
     */
    protected function createTestDouble(string $type): object
    {
        return $this->createStub($type);
    }

    /**
     * @psalm-param list<class-string> $interfaces
     */
    protected function createTestDoubleForIntersection(array $interfaces): object
    {
        return $this->createStubForIntersectionOfInterfaces($interfaces);
    }
}
