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

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('I_DO_NOT_EXIST')]
class Issue1374Test extends TestCase
{
    protected function setUp(): void
    {
        print __FUNCTION__;
    }

    protected function tearDown(): void
    {
        print __FUNCTION__;
    }

    public function testSomething(): void
    {
        $this->fail('This should not be reached');
    }
}
