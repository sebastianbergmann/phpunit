<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ListingTestsAndGroups;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

abstract class ExampleAbstractTestCase extends TestCase
{
    #[Group('abstract-one')]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
