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

use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use function is_int;
use function trigger_error;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/ClassWhoseConstructorTriggersAWarning.php';

final class IssuesTriggeredWhileArgumentsAreGeneratedTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        trigger_error('notice triggered by generator method', E_USER_NOTICE);

        return Generators::integers(0, 9);
    }

    #[Property(examples: 5)]
    public function testConstructorOfParameterTriggersAWarning(ClassWhoseConstructorTriggersAWarning $object): void
    {
        $this->assertTrue(is_int($object->value()));
    }

    #[Property(examples: 5)]
    public function testClosurePassedToMapTriggersADeprecation(
        #[ForAll(static function (): ValueGenerator
        {
            return Generators::integers(0, 9)->map(
                static function (int $value): int
                {
                    trigger_error('deprecation triggered by closure passed to map()', E_USER_DEPRECATED);

                    return $value;
                },
            );
        })]
        int $value,
    ): void {
        $this->assertTrue(is_int($value));
    }

    #[Property(examples: 5)]
    public function testGeneratorMethodTriggersANotice(#[ForAll('integers')] int $value): void
    {
        $this->assertTrue(is_int($value));
    }
}
