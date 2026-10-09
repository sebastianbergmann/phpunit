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
use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\TestCase;

final class RequirementMetAtRunTimeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/function_declared_at_run_time.php';
    }

    #[Property(examples: 5)]
    #[RequiresFunction('PHPUnit\TestFixture\PropertyBasedTesting\identity')]
    public function testRequiresAFunctionThatIsDeclaredAtRunTime(int $value): void
    {
        $this->assertSame($value, identity($value));
    }

    #[Property]
    #[RequiresFunction('PHPUnit\TestFixture\PropertyBasedTesting\identity')]
    public function testHasAParameterForWhichNoGeneratorCanBeDerived(callable $callable): void
    {
        $this->assertTrue(true);
    }
}
