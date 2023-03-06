<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use Exception;
use PHPUnit\Framework\TestCase;

final class ExpectingExceptionsTest extends TestCase
{
    public function testPassesWhenExpectedExceptionIsThrown(): void
    {
        $this->expectException(Exception::class);

        throw new Exception;
    }

    public function testFailsWhenExpectedExceptionIsNotThrown(): void
    {
        $this->expectException(Exception::class);
    }

    public function testPassesWhenExpectedExceptionIsThrownAndHasMessageThatIsOrContainsExpectedMessage(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('message');

        throw new Exception('message');
    }

    public function testFailsWhenExpectedExceptionIsThrownAndDoesNotHaveMessageThatIsOrContainsExpectedMessage(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('message');

        throw new Exception;
    }

    public function testPassesWhenExpectedExceptionIsThrownAndHasMessageThatMatchesRegularExpression(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/message/');

        throw new Exception('message');
    }

    public function testFailsWhenExpectedExceptionIsThrownAndDoesNotHaveMessageThatMatchesRegularExpression(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/message/');

        throw new Exception;
    }

    public function testPassesWhenExpectedExceptionIsThrownAndHasExpectedCode(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1234);

        throw new Exception(code: 1234);
    }

    public function testFailsWhenExpectedExceptionIsThrownAndDoesNotHaveExpectedCode(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1234);

        throw new Exception;
    }

    public function testPassesWhenExpectedExceptionObjectIsThrown(): void
    {
        $this->expectExceptionObject(new Exception('message', 1234));

        throw new Exception('message', 1234);
    }

    public function testFailsWhenExpectedExceptionObjectIsNotThrown(): void
    {
        $this->expectExceptionObject(new Exception('message', 1234));
    }
}
