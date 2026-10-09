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
use function trigger_error;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class IssuesTriggeredWhileArgumentsOfRejectedExamplesAreGeneratedTest extends TestCase
{
    #[Property(examples: 1)]
    public function testClosurePassedToFilterTriggersADeprecationAndRejectsEveryValue(
        #[ForAll(static function (): ValueGenerator
        {
            return Generators::integers(0, 9)->filter(
                static function (int $value): bool
                {
                    trigger_error('deprecation triggered for a value that is rejected by filter()', E_USER_DEPRECATED);

                    return false;
                },
            );
        })]
        int $value,
    ): void {
        $this->assertSame(0, $value);
    }

    #[Property(examples: 5)]
    public function testClosurePassedToMapTriggersANoticeForTheValuesThatAreRejectedByAssume(
        #[ForAll(static function (): ValueGenerator
        {
            return Generators::integers(0, 9)->map(
                static function (int $value): int
                {
                    if ($value % 2 !== 0) {
                        trigger_error('notice triggered for a value that is rejected by assume()', E_USER_NOTICE);
                    }

                    return $value;
                },
            );
        })]
        int $value,
    ): void {
        $this->assume($value % 2 === 0);

        $this->assertSame(0, $value % 2);
    }
}
