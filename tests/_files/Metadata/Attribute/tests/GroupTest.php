<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;

#[Group('group')]
#[Ticket('ticket')]
final class GroupTest extends TestCase
{
    #[Group('another-group')]
    #[Ticket('another-ticket')]
    public function testOne(): void
    {
    }
}
