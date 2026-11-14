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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/ValidatedEmailAddress.php';

final class ConstructorThatRaisesExceptionForGeneratedArgumentsTest extends TestCase
{
    #[Property]
    public function testObjectOfClassWhoseConstructorValidatesItsArguments(ValidatedEmailAddress $emailAddress): void
    {
        $this->assertStringContainsString('@', $emailAddress->value);
    }
}
