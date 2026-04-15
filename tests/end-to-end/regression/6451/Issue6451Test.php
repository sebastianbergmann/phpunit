<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6451;

use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\TestCase;

final class Issue6451Test extends TestCase
{
    #[RequiresPhp('>= 8')]
    public function testIncompletePhpVersion(): void
    {
        $this->assertTrue(true);
    }

    #[RequiresPhpunit('>= 10')]
    public function testIncompletePhpunitVersion(): void
    {
        $this->assertTrue(true);
    }

    #[RequiresPhpExtension('json', '>= 1')]
    public function testIncompletePhpExtensionVersion(): void
    {
        $this->assertTrue(true);
    }

    #[RequiresPhp('>= 8.0.0')]
    #[RequiresPhpunit('>= 10.0.0')]
    #[RequiresPhpExtension('json', '>= 1.0.0')]
    public function testCompleteVersions(): void
    {
        $this->assertTrue(true);
    }
}
