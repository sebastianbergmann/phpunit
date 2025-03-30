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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CoveredClass::class)]
#[UsesClass(CoveredClass::class)]
#[Group('the-group')]
#[Ticket('the-ticket')]
#[Small]
final class SmallGroupAnnotationsTest extends TestCase
{
    #[Group('another-group')]
    #[Ticket('another-ticket')]
    public function testOne(): void
    {
    }
}
