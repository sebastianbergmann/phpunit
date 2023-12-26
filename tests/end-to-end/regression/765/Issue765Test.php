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

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class Issue765Test extends TestCase
{
    public static function dependentProvider(): void
    {
        throw new Exception;
    }

    public function testDependee(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testDependee')]
    #[DataProvider('dependentProvider')]
    public function testDependent($a): void
    {
        $this->assertTrue(true);
    }
}
