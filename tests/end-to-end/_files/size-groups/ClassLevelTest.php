<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\SizeGroups;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('small')]
#[Group('medium')]
#[Group('large')]
final class ClassLevelTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
