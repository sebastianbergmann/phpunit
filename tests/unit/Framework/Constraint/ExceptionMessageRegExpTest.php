<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\TestCase;

class ExceptionMessageRegExpTest extends TestCase
{
    public function testRegexMessage(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessageRegExp('/^A polymorphic \w+ message/');
        throw new \Exception('A polymorphic exception message');
    }

    public function testRegexMessageExtreme(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessageRegExp('/^a poly[a-z]+ [a-zA-Z0-9_]+ me(s){2}age$/i');
        throw new \Exception('A polymorphic exception message');
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testMessageXdebugScreamCompatibility(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessageRegExp('#Screaming preg_match#');
        \ini_set('xdebug.scream', '1');

        throw new \Exception('Screaming preg_match');
    }

    public function testSimultaneousLiteralAndRegExpExceptionMessage(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessageRegExp('/^A variadic \w+ message/');
        throw new \Exception('A variadic exception message');
    }
}
