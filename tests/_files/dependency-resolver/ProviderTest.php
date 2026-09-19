<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DependencyResolver;

use PHPUnit\Framework\TestCase;
use stdClass;

final class ProviderTest extends TestCase
{
    public function testReturnsObject(): stdClass
    {
        $object        = new stdClass;
        $object->child = new stdClass;

        return $object;
    }

    public function testReturnsVoid(): void
    {
    }
}
