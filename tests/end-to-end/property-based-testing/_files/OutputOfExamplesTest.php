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

final class OutputOfExamplesTest extends TestCase
{
    private static int $example = 0;

    #[Property(examples: 3)]
    public function testPrintsOutputForEveryExample(int $value): void
    {
        self::$example++;

        print 'example ' . self::$example . "\n";

        $this->provideAdditionalInformation('additional information of example ' . self::$example);

        $this->assertIsInt($value);
    }
}
