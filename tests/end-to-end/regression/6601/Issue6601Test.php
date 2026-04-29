<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6601;

use PHPUnit\Framework\TestCase;
use stdClass;

interface Customizable
{
    public function customize(): void;
}

final class Issue6601Test extends TestCase
{
    public function testOne(): void
    {
        $object = new class implements Customizable
        {
            public function customize(?stdClass $custom = null): void
            {
            }
        };

        self::createMock($object::class);
    }
}
