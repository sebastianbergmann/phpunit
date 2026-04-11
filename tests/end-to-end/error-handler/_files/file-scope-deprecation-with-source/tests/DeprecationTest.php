<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler\FileScopeDeprecationWithSource;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

require __DIR__ . '/../src/DeprecatedFunction.php';

deprecated_function();
trigger_error('file scope user deprecation', E_USER_DEPRECATED);

final class DeprecationTest extends TestCase
{
    public function testSuccess(): void
    {
        $this->assertTrue(true);
    }
}
