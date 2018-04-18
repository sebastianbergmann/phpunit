<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foo\DataProviderIssue2833;

use PHPUnit\Framework\TestCase;

class SecondTest extends TestCase
{
    const DUMMY = 'dummy';

    public function testSecond(): void
    {
        $this->assertTrue(true);
    }
}
