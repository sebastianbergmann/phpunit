<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Baseline;

use PHPUnit\Framework\TestCase;

final class Test extends TestCase
{
    public function testDeprecation(): void
    {
        (new Source)->triggerDeprecation();

        $this->assertTrue(true);
    }

    public function testNotice(): void
    {
        (new Source)->triggerNotice();

        $this->assertTrue(true);
    }

    public function testWarning(): void
    {
        (new Source)->triggerWarning();

        $this->assertTrue(true);
    }

    public function testPhpDeprecation(): void
    {
        (new Source)->triggerPhpDeprecation();

        $this->assertTrue(true);
    }

    public function testPhpNoticeAndWarning(): void
    {
        (new Source)->triggerPhpNoticeAndWarning();

        $this->assertTrue(true);
    }
}
