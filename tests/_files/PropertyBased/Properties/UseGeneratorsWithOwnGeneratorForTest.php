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
use PHPUnit\Framework\Attributes\UseGenerators;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;

#[UseGenerators(EmailAddressGenerators::class)]
final class UseGeneratorsWithOwnGeneratorForTest extends TestCase
{
    use GeneratorForEmailAddresses;

    #[Property]
    #[UseGenerators(OtherEmailAddressGenerators::class)]
    public function testUsesTheGeneratorOfTheTestCaseClass(EmailAddress $emailAddress): void
    {
    }
}
