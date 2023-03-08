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

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\TestFixture\CoveredClass
 *
 * @uses \PHPUnit\TestFixture\CoveredClass
 *
 * @group the-group
 *
 * @ticket the-ticket
 *
 * @small
 */
final class SmallGroupAnnotationsTest extends TestCase
{
    /**
     * @group another-group
     *
     * @ticket another-ticket
     */
    public function testOne(): void
    {
    }
}
