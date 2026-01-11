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

use PHPUnit\Framework\Attributes\RequiresPackageVersion;
use PHPUnit\Framework\TestCase;

final class RequiresPackageVersionTest extends TestCase
{
    #[RequiresPackageVersion('phpunit/php-invoker', '^6.0')]
    public function testPackageVersionSatisfied(): void
    {
    }

    #[RequiresPackageVersion('phpunit/php-invoker', '^5.0')]
    public function testPackageVersionNotSatisfied(): void
    {
    }

    #[RequiresPackageVersion('some/non-existent-package', '^1.0')]
    public function testPackageNotInstalled(): void
    {
    }

    #[RequiresPackageVersion('phpunit/php-invoker', '^')]
    public function testVersionNotValid(): void
    {
    }
}
