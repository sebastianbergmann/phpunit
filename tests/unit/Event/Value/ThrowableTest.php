<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use Exception;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Filter;

#[CoversClass(Throwable::class)]
#[CoversClass(ThrowableBuilder::class)]
#[Small]
final class ThrowableTest extends TestCase
{
    public function testCanBeCreatedForThrowableWithoutPrevious(): void
    {
        $e = new Exception('message', 123, null);
        $t = ThrowableBuilder::from($e);

        $this->assertSame(Exception::class, $t->className());
        $this->assertSame('message', $t->message());
        $this->assertSame("Exception: message\n", $t->description());
        $this->assertSame(Filter::stackTraceFromThrowableAsString($e), $t->stackTrace());
        $this->assertFalse($t->hasPrevious());

        $this->expectException(NoPreviousThrowableException::class);

        $t->previous();
    }

    public function testCanBeCreatedForThrowableWithPrevious(): void
    {
        $first  = new Exception('first message', 123, null);
        $second = new Exception('second message', 456, $first);
        $t      = ThrowableBuilder::from($second);

        $this->assertSame(Exception::class, $t->className());
        $this->assertSame('second message', $t->message());
        $this->assertSame("Exception: second message\n", $t->description());
        $this->assertSame(Filter::stackTraceFromThrowableAsString($second, false), $t->stackTrace());
        $this->assertTrue($t->hasPrevious());

        $previous = $t->previous();

        $this->assertSame(Exception::class, $previous->className());
        $this->assertSame('first message', $previous->message());
        $this->assertSame("Exception: first message\n", $previous->description());
        $this->assertSame(Filter::stackTraceFromThrowableAsString($first), $previous->stackTrace());

        $this->assertStringMatchesFormat(
            <<<'EOD'
Exception: second message

%A
Caused by
Exception: first message

%A
EOD
            ,
            $t->asString(),
        );
    }
}
