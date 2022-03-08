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

use Error;
use PHPUnit\Framework\TestCase;

class StopOnErrorTestSuite extends TestCase
{
    public function testIncomplete(): void
    {
        $this->markTestIncomplete();
    }

    public function testWithError(): void
    {
        $this->assertTrue(true);

        throw new Error('StopOnErrorTestSuite_error');
    }

    public function testThatIsNeverReached(): void
    {
        $this->assertTrue(true);
    }
}
