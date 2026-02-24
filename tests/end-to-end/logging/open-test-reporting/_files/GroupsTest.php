<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Basic;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('one')]
class GroupsTest extends TestCase
{
    #[Group('two')]
    public function testWithClassAndMethodGroup(): void
    {
        $this->assertTrue(true);
    }

    #[Group('two')]
    #[Group('three')]
    public function testWithClassGroupOnly(): void
    {
        $this->assertTrue(true);
    }
}
