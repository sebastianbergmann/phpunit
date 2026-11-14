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

use function ob_start;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class OutputBufferingOfGeneratorTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integersWhoseGenerationStartsOutputBuffering(): ValueGenerator
    {
        return Generators::integers(0, 9)->map(
            static function (int $value): int
            {
                ob_start();

                print 'printed while a value is generated';

                return $value;
            },
        );
    }

    #[Property]
    public function testGeneratorDoesNotEndTheOutputBufferingThatItStarted(#[ForAll('integersWhoseGenerationStartsOutputBuffering')] int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
