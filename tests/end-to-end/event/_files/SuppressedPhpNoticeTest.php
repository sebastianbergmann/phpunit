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

use function error_reporting;
use PHPUnit\Framework\TestCase;

final class SuppressedPhpNoticeTest extends TestCase
{
    private int $oldErrorReportingLevel = E_ALL;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oldErrorReportingLevel = error_reporting(E_ALL ^ E_NOTICE);
    }

    protected function tearDown(): void
    {
        error_reporting($this->oldErrorReportingLevel);
        parent::tearDown();
    }

    public function testPhpNotice(): void
    {
        $f = static function (): void
        {
        };

        $a = &$f();

        $this->assertTrue(true);
    }
}
