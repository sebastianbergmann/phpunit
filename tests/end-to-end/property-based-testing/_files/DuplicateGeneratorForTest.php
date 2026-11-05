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
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EmailAddress.php';

final class DuplicateGeneratorForTest extends TestCase
{
    /**
     * @return ValueGenerator<EmailAddress>
     */
    #[GeneratorFor(EmailAddress::class)]
    public static function first(): ValueGenerator
    {
        return Generators::constant(EmailAddress::fromString('first@example.com'));
    }

    /**
     * @return ValueGenerator<EmailAddress>
     */
    #[GeneratorFor(EmailAddress::class)]
    public static function second(): ValueGenerator
    {
        return Generators::constant(EmailAddress::fromString('second@example.com'));
    }

    #[Property]
    public function testOne(EmailAddress $emailAddress): void
    {
    }
}
