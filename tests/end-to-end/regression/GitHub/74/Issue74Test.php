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

use PHPUnit\Framework\TestCase;

class Issue74Test extends TestCase
{
    public function testCreateAndThrowNewExceptionInProcessIsolation(): void
    {
        require_once __DIR__ . '/NewException.php';

        throw new NewException('Testing GH-74');
    }
}
