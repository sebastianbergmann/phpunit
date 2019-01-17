<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foo\DataProviderIssue2922;

use PHPUnit\Framework\TestCase;

// the name of the class cannot match file name - if they match all is fine
class SecondHelloWorldTest extends TestCase
{
    public function testSecond(): void
    {
        $this->assertTrue(true);
    }
}
