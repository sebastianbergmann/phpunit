<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5456;

use function ob_end_clean;
use function ob_start;
use PHPUnit\Framework\TestCase;

final class Issue5456Test extends TestCase
{
    protected function tearDown(): void
    {
        ob_end_clean();
    }

    public function testOne(): void
    {
        $this->assertTrue(true);

        ob_start();
    }
}
