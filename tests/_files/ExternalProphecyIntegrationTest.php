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
use Prophecy\PhpUnit\ProphecyTrait;

final class ExternalProphecyIntegrationTest extends TestCase
{
    use ProphecyTrait;

    public function testOne(): void
    {
        $prophecy = $this->prophesize(\PHPUnit\TestFixture\AnInterface::class);
        $prophecy->doSomething()->willReturn('result')->shouldBeCalled();

        $revelation = $prophecy->reveal();

        $this->assertSame('result', $revelation->doSomething());
    }
}
