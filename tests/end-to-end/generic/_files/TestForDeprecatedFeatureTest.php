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

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;

final class TestForDeprecatedFeatureTest extends TestCase
{
    #[IgnoreDeprecations]
    public function testExpectationOnExactDeprecationMessageWorksWhenExpectedDeprecationIsTriggered(): void
    {
        $this->expectUserDeprecationMessage('message');

        @trigger_error('message', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testExpectationsOnExactDeprecationMessagesWorkWhenExpectedDeprecationsAreTriggered(): void
    {
        $this->expectUserDeprecationMessage('message');
        $this->expectUserDeprecationMessage('another message');

        @trigger_error('message', E_USER_DEPRECATED);
        @trigger_error('another message', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testExpectationOnExactDeprecationMessageWorksWhenExpectedDeprecationIsNotTriggered(): void
    {
        $this->expectUserDeprecationMessage('message');
    }

    #[IgnoreDeprecations]
    public function testExpectationOnExactDeprecationMessageWorksWhenUnexpectedDeprecationIsTriggered(): void
    {
        $this->expectUserDeprecationMessage('message');

        @trigger_error('another message', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testExpectationOnDeprecationMessageMatchingRegularExpressionWorksWhenExpectedDeprecationIsTriggered(): void
    {
        $this->expectUserDeprecationMessageMatches('/message/');

        @trigger_error('...message...', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testExpectationsOnDeprecationMessagesMatchingRegularExpressionsWorkWhenExpectedDeprecationsAreTriggered(): void
    {
        $this->expectUserDeprecationMessageMatches('/foo/');
        $this->expectUserDeprecationMessageMatches('/bar/');

        @trigger_error('...foo...', E_USER_DEPRECATED);
        @trigger_error('...bar...', E_USER_DEPRECATED);
    }

    #[IgnoreDeprecations]
    public function testExpectationOnDeprecationMessageMatchingRegularExpressionWorksWhenExpectedDeprecationIsNotTriggered(): void
    {
        $this->expectUserDeprecationMessageMatches('/message/');
    }

    #[IgnoreDeprecations]
    public function testExpectationOnDeprecationMessageMatchingRegularExpressionWorksWhenUnepectedDeprecationIsTriggered(): void
    {
        $this->expectUserDeprecationMessageMatches('/message/');

        @trigger_error('something else', E_USER_DEPRECATED);
    }
}
