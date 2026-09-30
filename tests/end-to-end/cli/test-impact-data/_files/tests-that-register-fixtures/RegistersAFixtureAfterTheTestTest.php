<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

require_once __DIR__ . '/TestCaseThatRegistersAFixture.php';

final class RegistersAFixtureAfterTheTestTest extends TestCaseThatRegistersAFixture
{
    public function testAdds(): void
    {
        $this->assertSame(3, (new Calculator)->add(1, 2));
    }
}
