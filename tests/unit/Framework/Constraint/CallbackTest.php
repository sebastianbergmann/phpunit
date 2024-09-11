<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(Callback::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class CallbackTest extends TestCase
{
    public function testCanBeEvaluated(): void
    {
        $this->assertTrue($this->acceptingCallbackConstraint()->evaluate('actual', returnResult: true));
        $this->assertFalse($this->rejectingCallbackConstraint()->evaluate('actual', returnResult: true));

        try {
            $this->rejectingCallbackConstraint()->evaluate('actual');
        } catch (ExpectationFailedException $e) {
            $this->assertSame('Failed asserting that \'actual\' is accepted by specified callback.', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('is accepted by specified callback', $this->acceptingCallbackConstraint()->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, $this->acceptingCallbackConstraint());
    }

    public function testIsVariadic(): void
    {
        $class = new class
        {
            public function __invoke(string ...$values): void
            {
            }
        };

        $this->assertTrue((new Callback($class))->isVariadic());
    }

    public function testIsNotVariadic(): void
    {
        $class = new class
        {
            public function __invoke(string $value): void
            {
            }
        };

        $this->assertFalse((new Callback($class))->isVariadic());
    }

    private function acceptingCallbackConstraint(): Callback
    {
        return new Callback(static fn (): bool => true);
    }

    private function rejectingCallbackConstraint(): Callback
    {
        return new Callback(static fn (): bool => false);
    }
}
