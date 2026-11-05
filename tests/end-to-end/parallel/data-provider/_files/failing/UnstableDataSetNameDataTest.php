<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelDataProvider;

use function uniqid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UnstableDataSetNameDataTest extends TestCase
{
    /**
     * A data provider that names its data set differently on every
     * invocation: the name the main process selects is never the one a
     * worker process' invocation provides.
     */
    public static function providerWithAnUnstableDataSetName(): array
    {
        return [
            uniqid('run-', true) => [true],
        ];
    }

    #[DataProvider('providerWithAnUnstableDataSetName')]
    public function testWithADataSetWhoseNameIsUnstable(bool $value): void
    {
        $this->assertTrue($value);
    }
}
