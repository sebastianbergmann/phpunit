<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use const DIRECTORY_SEPARATOR;
use function dirname;
use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileOutputRestriction::class)]
#[UsesClass(FileOutputTarget::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
final class FileOutputRestrictionTest extends TestCase
{
    public function testHasDirectory(): void
    {
        $this->assertSame($this->directory(), $this->restriction()->directory());
    }

    public function testAllowsPathsInsideTheDirectory(): void
    {
        $restriction = $this->restriction();

        $this->assertTrue($restriction->allows($this->directory()));
        $this->assertTrue($restriction->allows($this->directory() . DIRECTORY_SEPARATOR . 'file.txt'));
        $this->assertTrue($restriction->allows($this->directory() . '/does-not-exist/file.txt'));
        $this->assertTrue($restriction->allows($this->directory() . '/does-not-exist/../file.txt'));
    }

    public function testDoesNotAllowPathsOutsideTheDirectory(): void
    {
        $restriction = $this->restriction();

        $this->assertFalse($restriction->allows(dirname($this->directory())));
        $this->assertFalse($restriction->allows(dirname($this->directory()) . DIRECTORY_SEPARATOR . 'file.txt'));
        $this->assertFalse($restriction->allows($this->directory() . '/../file.txt'));
        $this->assertFalse($restriction->allows($this->directory() . '/does-not-exist/../../file.txt'));
        $this->assertFalse($restriction->allows($this->directory() . '-sibling/file.txt'));
    }

    public function testAllowsStandardOutputAndStandardErrorStreams(): void
    {
        $restriction = $this->restriction();

        $this->assertTrue($restriction->allows('php://stdout'));
        $this->assertTrue($restriction->allows('php://stderr'));
    }

    public function testDoesNotAllowOtherStreams(): void
    {
        $restriction = $this->restriction();

        $this->assertFalse($restriction->allows('socket://localhost:1234'));
        $this->assertFalse($restriction->allows('php://memory'));
        $this->assertFalse($restriction->allows('php://filter/resource=' . $this->directory() . '/file.txt'));
        $this->assertFalse($restriction->allows('file://' . $this->directory() . '/file.txt'));
    }

    public function testReportsTargetsThatAreNotAllowed(): void
    {
        $inside  = new FileOutputTarget('inside', $this->directory() . '/inside.txt');
        $outside = new FileOutputTarget('outside', dirname($this->directory()) . '/outside.txt');
        $stream  = new FileOutputTarget('stream', 'socket://localhost:1234');
        $stdout  = new FileOutputTarget('stdout', 'php://stdout');

        $this->assertSame(
            [$outside, $stream],
            $this->restriction()->violations([$inside, $outside, $stream, $stdout]),
        );

        $this->assertSame([], $this->restriction()->violations([]));
    }

    private function restriction(): FileOutputRestriction
    {
        return new FileOutputRestriction($this->directory());
    }

    /**
     * @return non-empty-string
     */
    private function directory(): string
    {
        $directory = realpath(__DIR__);

        $this->assertNotFalse($directory);

        return $directory;
    }
}
