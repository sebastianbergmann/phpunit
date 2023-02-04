<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox;

use PHPUnit\Framework\TestCase;

final class RiskyTest extends TestCase
{
    public function test_this_is_a_useless_test_that_does_not_test_anything(): void
    {
    }
}
