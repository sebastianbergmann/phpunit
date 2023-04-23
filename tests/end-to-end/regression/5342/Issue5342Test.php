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

use function ob_end_clean;
use PHPUnit\Framework\TestCase;

class Issue5342Test extends TestCase
{
    public function testFailure(): void
    {
        ob_end_clean();
        $this->assertTrue(false);
    }
}
