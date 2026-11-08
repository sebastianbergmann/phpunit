<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class GeneratorFromSetUpBeforeClassTest extends TestCase
{
    private static int $preparations = 0;

    /**
     * @var list<non-empty-string>
     */
    private static array $identifiers = [];

    /**
     * @return ValueGenerator<non-empty-string>
     */
    public static function identifiers(): ValueGenerator
    {
        return Generators::elements(...self::$identifiers);
    }

    public static function setUpBeforeClass(): void
    {
        self::$preparations++;

        self::$identifiers = ['identifier of preparation ' . self::$preparations];
    }

    public static function tearDownAfterClass(): void
    {
        self::$identifiers = [];
    }

    #[Property]
    public function testIdentifierWasSetUpWhenTheTestClassWasPrepared(#[ForAll('identifiers')] string $identifier): void
    {
        $this->assertContains($identifier, self::$identifiers);
    }
}
