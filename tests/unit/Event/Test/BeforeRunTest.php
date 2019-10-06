<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\Run\BeforeRun;
use PHPUnit\Event\Run\BeforeRunType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Test\BeforeRun
 */
final class BeforeRunTest extends TestCase
{
    public function testTypeIsBeforeTest(): void
    {
        $event = new BeforeRun();

        $this->assertTrue($event->type()->is(new BeforeRunType()));
    }
}
