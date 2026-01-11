<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\RequiresPackageVersion;
use PHPUnit\Framework\TestCase;

#[RequiresPackageVersion('phpunit/php-invoker', '^6.0')]
final class RequiresPackageVersionTest extends TestCase
{
    #[RequiresPackageVersion('phpunit/php-invoker', '^5.0')]
    public function testOne(): void
    {
    }

    #[RequiresPackageVersion('phpunit/php-invoker', '^6.0')]
    public function testTwo(): void
    {
    }
}
