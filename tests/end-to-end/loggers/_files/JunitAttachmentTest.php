<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\ReportAttachmentProviding;
use PHPUnit\Framework\TestCase;

class JunitAttachmentTest extends TestCase implements ReportAttachmentProviding
{
    private $attachments = [];

    public function testOne(): void
    {
        $this->attachments = [
            '/tmp/path/to/example.png',
            '/tmp/there/can/be/more/than/one/attachment.txt',
        ];

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->attachments = [];

        $this->assertTrue(true);
    }

    public function testWithOutput(): void
    {
        $this->attachments = [];

        $this->assertTrue(true);

        print 'test output';
    }

    public function testWithOutputAndAttachment(): void
    {
        $this->attachments = [
            '/tmp/path/to/example.png',
        ];

        $this->assertTrue(true);

        print 'test output';
    }

    public function testFailure(): void
    {
        $this->attachments = [
            '/tmp/path/to/failure.png',
        ];

        $this->assertTrue(false);
    }

    public function provideTestResultAttachmentPaths(): array
    {
        return $this->attachments;
    }
}
