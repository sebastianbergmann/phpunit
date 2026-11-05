<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\NameResolution;

use const PHP_EOL;
use function strlen;
use DateTimeImmutable;
use PHPUnit\TestFixture\PropertyBased\{Currency as GroupedCurrency, EnumerationWithoutCases};
use PHPUnit\TestFixture\PropertyBased\Currency;
use PHPUnit\TestFixture\PropertyBased\Currency as Money;

final class ClassWithImports extends ParentClass
{
    public function method(): void
    {
    }
}

use PHPUnit\TestFixture\PropertyBased\ImportedAfterTheClass;
