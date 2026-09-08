<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox\TestThatNeverStarted;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DependencyTest extends TestCase
{
    public function testProducer(): void
    {
        $this->markTestSkipped('the test that is depended on');
    }

    #[Depends('testProducer')]
    public function testConsumer(): void
    {
        $this->assertTrue(true);
    }
}
