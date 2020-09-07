<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class SpyTest extends TestCase
{
    public function testIntegration(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($spy = $this->any())
             ->method('foo');

        $mock->foo([
            'attachments' => [
                'attachment1',
                'attachment2',
            ],
        ]);

        $invocationParams = $spy->getInvocations()[0]->getParameters();
        $this->assertContains('attachment2', $invocationParams[0]['attachments']);
    }
}
