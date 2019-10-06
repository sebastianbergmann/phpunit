<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\NamedType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\TestSuite\AfterTestSuite
 */
final class AfterTestSuiteTest extends TestCase
{
    public function testTypeIsTestAfterTestSuite(): void
    {
        $event = new AfterTestSuite();

        $this->assertTrue($event->type()->is(new NamedType('after-test-suite')));
    }
}
