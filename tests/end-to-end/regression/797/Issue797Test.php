<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue797Test extends TestCase
{
    protected $preserveGlobalState = false;

    public function testBootstrapPhpIsExecutedInIsolation(): void
    {
        $this->assertEquals(GITHUB_ISSUE, 797);
    }
}
