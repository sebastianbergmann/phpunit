<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use stdClass;

class Issue2137Test extends TestCase
{
    public static function provideBrandService()
    {
        return [
            // [true, true]
            new stdClass, // not valid
        ];
    }

    /**
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    #[DataProvider('provideBrandService')]
    public function testBrandService($provided, $expected): void
    {
        $this->assertSame($provided, $expected);
    }

    /**
     * @throws \Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    #[DataProvider('provideBrandService')]
    public function testSomethingElseInvalid($provided, $expected): void
    {
        $this->assertSame($provided, $expected);
    }
}
