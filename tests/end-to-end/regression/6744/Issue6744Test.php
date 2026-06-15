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

use function getenv;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\TestCase;

final class Issue6744Test extends TestCase
{
    #[WithEnvironmentVariable('FOO', '')]
    public function testEnvironmentVariableCanBeSetToEmptyString(): void
    {
        $this->assertSame('', $_ENV['FOO']);
        $this->assertSame('', getenv('FOO'));
    }
}
