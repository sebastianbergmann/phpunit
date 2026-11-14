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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExampleThatEndsBeforeItIsPreparedTest extends TestCase
{
    protected function setUp(): void
    {
        if ($this->name() === 'testFailureInSetUp') {
            $this->fail('failure in setUp()');
        }

        throw new RuntimeException('error in setUp()');
    }

    #[Property]
    public function testErrorInSetUp(int $value): void
    {
        $this->assertIsInt($value);
    }

    #[Property]
    public function testFailureInSetUp(int $value): void
    {
        $this->assertIsInt($value);
    }
}
