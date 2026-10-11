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

use PHPUnit\Framework\Attributes\GeneratorFor;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

final class EmailAddressGenerators
{
    /**
     * @return ValueGenerator<EmailAddress>
     */
    #[GeneratorFor(EmailAddress::class)]
    public static function emailAddresses(): ValueGenerator
    {
        return Generators::strings(alphabet: 'abcdefghijklmnopqrstuvwxyz', minimumLength: 1, maximumLength: 16)
            ->map(static function (string $localPart): EmailAddress
            {
                return EmailAddress::fromString($localPart . '@example.com');
            });
    }
}
