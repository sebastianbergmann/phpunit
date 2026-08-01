<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function base64_encode;
use function serialize;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(CommandStream::class)]
#[UsesClass(TestDescriptor::class)]
#[UsesClass(TestCaseDescriptor::class)]
#[Small]
final class CommandStreamTest extends TestCase
{
    public function testReadsBackTheCommandItEncoded(): void
    {
        $command = CommandStream::decode(
            CommandStream::encode(
                [
                    'command'   => 'runUnit',
                    'className' => WorkerFirstTest::class,
                    'tests'     => [$this->descriptor()],
                ],
            ),
        );

        $this->assertIsArray($command);
        $this->assertSame('runUnit', $command['command']);
        $this->assertContainsOnlyInstancesOf(TestCaseDescriptor::class, $command['tests']);
    }

    public function testEncodesACommandThatCarriesArbitraryBytesWithoutTheNewlineThatDelimitsIt(): void
    {
        $encoded = CommandStream::encode(
            [
                'command' => 'runUnit',
                'file'    => "/tmp/not\nvalid\xC0utf8.php",
            ],
        );

        $this->assertStringNotContainsString("\n", $encoded);

        $command = CommandStream::decode($encoded);

        $this->assertIsArray($command);
        $this->assertSame("/tmp/not\nvalid\xC0utf8.php", $command['file']);
    }

    public function testDoesNotDecodeALineThatIsNotBase64Encoded(): void
    {
        $this->assertNull(CommandStream::decode('not base64!'));
    }

    public function testDoesNotDecodeALineThatDoesNotUnserialize(): void
    {
        $this->assertNull(CommandStream::decode(base64_encode('not-a-serialized-command')));
    }

    public function testDoesNotDecodeAPayloadThatIsNotACommand(): void
    {
        $this->assertNull(CommandStream::decode(base64_encode(serialize(['tests' => []]))));
    }

    public function testDoesNotRestoreObjectsOfClassesOtherThanTheDescriptors(): void
    {
        $command = CommandStream::decode(
            base64_encode(
                serialize(
                    [
                        'command' => 'runUnit',
                        'tests'   => [new WorkerFirstTest('testStartsTheProcessLocalCounter')],
                    ],
                ),
            ),
        );

        $this->assertIsArray($command);
        $this->assertIsArray($command['tests']);
        $this->assertNotInstanceOf(WorkerFirstTest::class, $command['tests'][0]);
    }

    private function descriptor(): TestDescriptor
    {
        return TestDescriptor::from(
            new WorkerFirstTest('testStartsTheProcessLocalCounter'),
            WorkerFirstTest::class,
        );
    }
}
