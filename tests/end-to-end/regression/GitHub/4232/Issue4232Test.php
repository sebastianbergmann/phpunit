<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foo\Bar;

require_once __DIR__ . '/ParentIssue4232Test.php';

final class Issue4232Test extends ParentIssue4232Test
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
