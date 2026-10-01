<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Baseline\SeparateProcess;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class MethodInSeparateProcessTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testDeprecation(): void
    {
        $this->assertTrue((new Source)->triggerDeprecation());
    }
}
