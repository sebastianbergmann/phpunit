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

use PHPUnit\Framework\Attributes\GeneratorFor;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;
use PHPUnit\TestFixture\PropertyBased\Derivation\ExampleEmailAddress;

final class GeneratorForTest extends TestCase
{
    /**
     * @return ValueGenerator<EmailAddress>
     */
    #[GeneratorFor(EmailAddress::class)]
    public static function emailAddresses(): ValueGenerator
    {
        return Generators::constant(new ExampleEmailAddress);
    }
}
