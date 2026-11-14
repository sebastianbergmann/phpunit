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

use LogicException;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ExceptionWithoutMessageInAfterClassMethodTest extends TestCase
{
    #[AfterClass]
    public static function cleanUp(): void
    {
        throw new LogicException;
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testPasses(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
