<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Properties;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

final class PropertyWithUnmetRequirementsTest extends TestCase
{
    #[Property]
    #[RequiresPhpExtension('this-extension-does-not-exist')]
    public function testOne(mixed $value): void
    {
    }
}
