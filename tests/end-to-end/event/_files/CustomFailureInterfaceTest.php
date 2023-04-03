<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\TestCase;

final class CustomFailureInterfaceTest extends TestCase
{
    public function testOne(): void
    {
        $this->registerFailureType(CustomFailureInterface::class);

        $this->assertTrue(true);

        throw new CustomFailureException('this should be treated as a failure');
    }

    public function testTwo(): void
    {
        throw new CustomFailureException('this should be treated as an error');
    }
}
