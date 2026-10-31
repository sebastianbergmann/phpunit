<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\NameResolution\First {
    use PHPUnit\TestFixture\PropertyBased\Currency as Imported;

    final class ClassInFirstNamespace
    {
        public function method(): void
        {
        }
    }
}

namespace PHPUnit\TestFixture\PropertyBased\NameResolution\Second {
    use PHPUnit\TestFixture\PropertyBased\EnumerationWithoutCases as Imported;

    final class ClassInSecondNamespace
    {
        public function method(): void
        {
        }
    }
}
