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

use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\TestCase;

final class RequirementsWithInvalidVersionConstraintTest extends TestCase
{
    #[RequiresPhp('invalid-version')]
    public function testRequiresPhp(): void
    {
    }

    #[RequiresPhpunit('invalid-version')]
    public function testRequiresPhpunit(): void
    {
    }

    #[RequiresPhpExtension('json', 'invalid-version')]
    public function testRequiresPhpExtension(): void
    {
    }

    public function testWithoutVersionConstraint(): void
    {
    }
}
