<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5498;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

final class Test extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    #[Before]
    protected function before(): void
    {
    }

    #[After]
    protected function after(): void
    {
    }
}
