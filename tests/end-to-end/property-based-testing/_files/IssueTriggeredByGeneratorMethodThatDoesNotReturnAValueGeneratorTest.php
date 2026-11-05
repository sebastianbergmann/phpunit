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

use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class IssueTriggeredByGeneratorMethodThatDoesNotReturnAValueGeneratorTest extends TestCase
{
    public static function integers(): string
    {
        trigger_error('warning triggered by generator method', E_USER_WARNING);

        return 'not a value generator';
    }

    #[Property]
    public function testGeneratorMethodTriggersAWarning(#[ForAll('integers')] int $value): void
    {
        $this->assertTrue(true);
    }
}
