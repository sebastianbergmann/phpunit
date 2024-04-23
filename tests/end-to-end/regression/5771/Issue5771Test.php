<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5771;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class Issue5771Test extends TestCase
{
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        exit;
    }
}
