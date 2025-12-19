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
    public function test_expectException_and_expected_exception_is_thrown(): void
    {
        $this->expectException(Exception::class);

        throw new Exception;
    }

    public function test_expectException_and_expected_exception_is_not_thrown(): void
    {
        $this->expectException(Exception::class);
    }

    public function test_expectException_and_expectExceptionMessage_and_expected_exception_is_thrown_and_has_expected_message(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('message');

        throw new Exception('message');
    }

    public function test_expectExceptionMessage_and_exception_is_thrown_and_has_expected_message(): void
    {
        $this->expectExceptionMessage('message');

        throw new Exception('message');
    }

    public function test_expectException_and_expectExceptionMessage_and_expected_exception_is_thrown_but_does_not_have_expected_message(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('message');

        throw new Exception;
    }

    public function test_expectExceptionMessage_and_exception_is_thrown_but_does_not_have_expected_message(): void
    {
        $this->expectExceptionMessage('message');

        throw new Exception;
    }

    public function test_expectExceptionMessage_and_no_exception_is_thrown(): void
    {
        $this->expectExceptionMessage('message');
    }

    public function test_expectException_and_expectExceptionMessageMatches_and_expected_exception_is_thrown_and_has_expected_message(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/message/');

        throw new Exception('message');
    }

    public function test_expectExceptionMessageMatches_and_exception_is_thrown_and_has_expected_message(): void
    {
        $this->expectExceptionMessageMatches('/message/');

        throw new Exception('message');
    }

    public function test_expectException_and_expectExceptionMessageMatches_and_expected_exception_is_thrown_but_does_not_have_expected_message(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/message/');

        throw new Exception;
    }

    public function test_expectExceptionMessageMatches_and_exception_is_thrown_but_does_not_have_expected_message(): void
    {
        $this->expectExceptionMessageMatches('/message/');

        throw new Exception;
    }

    public function test_expectExceptionMessageMatches_and_no_exception_is_thrown(): void
    {
        $this->expectExceptionMessageMatches('/message/');
    }

    public function test_expectException_and_expectExceptionCode_and_expected_exception_is_thrown_and_has_expected_code(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1234);

        throw new Exception(code: 1234);
    }

    public function test_expectExceptionCode_and_exception_is_thrown_and_has_expected_code(): void
    {
        $this->expectExceptionCode(1234);

        throw new Exception(code: 1234);
    }

    public function test_expectException_and_expectExceptionCode_and_expected_exception_is_thrown_but_does_not_have_expected_code(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(1234);

        throw new Exception;
    }

    public function test_expectExceptionCode_and_exception_is_thrown_but_does_not_have_expected_code(): void
    {
        $this->expectExceptionCode(1234);

        throw new Exception;
    }

    public function test_expectExceptionCode_and_no_exception_is_thrown(): void
    {
        $this->expectExceptionCode(1234);
    }

    public function test_expectExceptionObject_and_expected_exception_is_thrown(): void
    {
        $this->expectExceptionObject(new Exception('message', 1234));

        throw new Exception('message', 1234);
    }

    public function test_expectExceptionObject_and_expected_exception_is_not_thrown(): void
    {
        $this->expectExceptionObject(new Exception('message', 1234));

        throw new Exception('not-the-message', 5678);
    }

    public function test_expectExceptionObject_and_no_exception_is_thrown(): void
    {
        $this->expectExceptionObject(new Exception('message', 1234));
    }
}
