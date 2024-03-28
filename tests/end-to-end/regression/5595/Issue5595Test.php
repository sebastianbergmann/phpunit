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

use function set_error_handler;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class Issue5595Test extends TestCase
{
    protected function setUp(): void
    {
        set_error_handler(static function (): bool
        {
            return true;
        });
    }

    #[RunInSeparateProcess]
    public function test(): void
    {
        $this->assertTrue(true);
    }
}
