<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\UseGenerators;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EmailAddress.php';

require_once __DIR__ . '/Customer.php';

require_once __DIR__ . '/EmailAddressGenerators.php';

require_once __DIR__ . '/OtherEmailAddressGenerators.php';

#[UseGenerators(EmailAddressGenerators::class)]
final class UseGeneratorsTest extends TestCase
{
    #[Property]
    public function testGeneratorOfTheClassThatIsUsedForTheTestCaseClassIsUsedForAConstructorParameter(Customer $customer): void
    {
        $this->assertStringEndsWith('@example.com', $customer->emailAddress()->asString());
    }

    #[Property]
    #[UseGenerators(OtherEmailAddressGenerators::class)]
    public function testGeneratorOfTheClassThatIsUsedForThePropertyTakesPrecedence(EmailAddress $emailAddress): void
    {
        $this->assertStringEndsWith('@example.org', $emailAddress->asString());
    }
}
