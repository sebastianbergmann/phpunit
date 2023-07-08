<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/mock-object')]
#[Medium]
#[RequiresPhpExtension('soap')]
#[TestDox('getMockFromWsdl()')]
final class GetMockFromWsdlTest extends TestCase
{
    #[TestDox('Creates mock object from WSDL file')]
    public function test_CreatesMockObjectFromWsdlFileWithNonNamespacedClassName(): void
    {
        $mock = $this->getMockFromWsdl(TEST_FILES_PATH . 'GoogleSearch.wsdl');

        $this->assertStringStartsWith(
            'Mock_GoogleSearch_',
            $mock::class,
        );
    }
}
