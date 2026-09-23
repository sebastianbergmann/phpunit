<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\ProcessOfOrigin;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class ProcessOfOriginTest extends TestCase
{
    public function testInThisProcess(): void
    {
        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    public function testInSeparateProcess(): void
    {
        $this->assertTrue(true);
    }
}
