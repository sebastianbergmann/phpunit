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
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EmailAddress.php';

require_once __DIR__ . '/Customer.php';

require_once __DIR__ . '/GeneratorForEmailAddresses.php';

final class GeneratorForTest extends TestCase
{
    use GeneratorForEmailAddresses;

    #[Property]
    public function testRegisteredGeneratorIsUsedForAParameter(EmailAddress $emailAddress): void
    {
        $this->assertStringEndsWith('@example.com', $emailAddress->asString());
    }

    #[Property]
    public function testRegisteredGeneratorIsUsedForAConstructorParameter(Customer $customer, EmailAddress $newAddress): void
    {
        $this->assertStringEndsWith('@example.com', $customer->emailAddress()->asString());

        $customer->changeEmailAddress($newAddress);

        $this->assertSame($newAddress, $customer->emailAddress());
    }
}
