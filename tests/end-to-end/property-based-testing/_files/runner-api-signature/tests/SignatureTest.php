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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SignatureTest extends TestCase
{
    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testRaisesAnException(int $value): void
    {
        throw new RuntimeException('message');
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testRaisesAnAnonymousException(int $value): void
    {
        throw new class('message') extends RuntimeException
        {
        };
    }

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testRaisesATestDoubleOfAnException(int $value): void
    {
        throw $this->createStub(RuntimeException::class);
    }
}
