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

use PHPUnit\Framework\Attributes\GeneratorFor;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;

abstract class ClassWithAbstractGeneratorFor
{
    /**
     * @return ValueGenerator<EmailAddress>
     */
    #[GeneratorFor(EmailAddress::class)]
    abstract public static function emailAddresses(): ValueGenerator;
}
