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
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;

final class PropertyInSubclassTest extends AbstractTestCaseWithGeneratorFor
{
    #[Property]
    public function testOne(EmailAddress $emailAddress): void
    {
    }
}
