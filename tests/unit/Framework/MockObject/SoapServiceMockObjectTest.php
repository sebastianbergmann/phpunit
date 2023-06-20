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
use PHPUnit\Framework\TestCase;

#[Group('test-doubles')]
#[Medium]
#[RequiresPhpExtension('soap')]
final class SoapServiceMockObjectTest extends TestCase
{
    public function testCanBeCreatedFromWsdlWithNonNamespacedClassName(): void
    {
        $mock = $this->getMockFromWsdl(TEST_FILES_PATH . 'GoogleSearch.wsdl', 'WsdlMock');

        $this->assertStringStartsWith(
            'Mock_WsdlMock_',
            $mock::class,
        );
    }

    public function testCanBeCreatedFromWsdlWithNamespacedClassName(): void
    {
        $mock = $this->getMockFromWsdl(TEST_FILES_PATH . 'GoogleSearch.wsdl', 'My\\Space\\WsdlMock');

        $this->assertStringStartsWith(
            'Mock_WsdlMock_',
            $mock::class,
        );
    }

    public function testCanBeCreatedFromWsdlFileWithStrangeCharactersInFilename(): void
    {
        $mock = $this->getMockFromWsdl(TEST_FILES_PATH . 'Go ogle-Sea.rch.wsdl');

        $this->assertStringStartsWith('Mock_GoogleSearch_', $mock::class);
    }
}
