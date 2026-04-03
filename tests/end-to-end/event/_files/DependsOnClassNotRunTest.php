<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/DependsOnClassNotRunTarget.php';

final class DependsOnClassNotRunTest extends TestCase
{
    #[DependsOnClass(DependsOnClassNotRunTarget::class)]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
