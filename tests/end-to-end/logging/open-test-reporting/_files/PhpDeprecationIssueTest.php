<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\OpenTestReporting;

use function utf8_encode;
use PHPUnit\Framework\TestCase;

final class PhpDeprecationIssueTest extends TestCase
{
    public function testOne(): void
    {
        utf8_encode('test');

        $this->assertTrue(true);
    }
}
