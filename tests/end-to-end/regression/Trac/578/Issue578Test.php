<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue578Test extends TestCase
{
    public function testNoticesDoublePrintStackTrace(): void
    {
        $this->iniSet('error_reporting', (string) (\E_ALL | \E_NOTICE));
        \trigger_error('Stack Trace Test Notice', \E_NOTICE);
    }

    public function testWarningsDoublePrintStackTrace(): void
    {
        $this->iniSet('error_reporting', (string) (\E_ALL | \E_NOTICE));
        \trigger_error('Stack Trace Test Notice', \E_WARNING);
    }

    public function testUnexpectedExceptionsPrintsCorrectly(): void
    {
        throw new Exception('Double printed exception');
    }
}
