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

use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class ExceptionMessageRegExpTest extends TestCase
{
    public function testRegexMessage(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/^A polymorphic \w+ message/');

        throw new \Exception('A polymorphic exception message');
    }

    public function testRegexMessageExtreme(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/^a poly[a-z]+ [a-zA-Z0-9_]+ me(s){2}age$/i');

        throw new \Exception('A polymorphic exception message');
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testMessageXdebugScreamCompatibility(): void
    {
        \ini_set('xdebug.scream', '1');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('#Screaming preg_match#');

        throw new \Exception('Screaming preg_match');
    }

    public function testRegExMessageCanBeExportedAsString(): void
    {
        $exceptionMessageReExp = new ExceptionMessageRegularExpression('/^a poly[a-z]+ [a-zA-Z0-9_]+ me(s){2}age$/i');

        $this->assertSame('exception message matches ', $exceptionMessageReExp->toString());
    }
}
