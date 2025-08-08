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

use PHPUnit\Framework\TestCase;

final class IssueTest6294 extends TestCase
{
    public function testOne(): void
    {
        require_once 'A.php';

        require_once 'B.php';

        $this->assertSame(1, 1);
    }
}
