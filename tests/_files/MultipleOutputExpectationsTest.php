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

use PHPUnit\Framework\TestCase;

final class MultipleOutputExpectationsTest extends TestCase
{
    public function testExpectOutputStringThenExpectOutputRegex(): void
    {
        $this->expectOutputString('foo');
        $this->expectOutputRegex('/.*/');

        print 'bar';
    }

    public function testExpectOutputRegexThenExpectOutputString(): void
    {
        $this->expectOutputRegex('/.*/');
        $this->expectOutputString('bar');

        print 'bar';
    }
}
