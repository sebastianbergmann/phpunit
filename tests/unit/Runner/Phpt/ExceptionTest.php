<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhptExternalFileCannotBeLoadedException::class)]
#[CoversClass(UnsupportedPhptSectionException::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/phpt')]
final class ExceptionTest extends TestCase
{
    public function testPhptExternalFileCannotBeLoadedExceptionMessage(): void
    {
        $exception = new PhptExternalFileCannotBeLoadedException('FILE', '/path/to/missing.php');

        $this->assertSame(
            'Could not load --FILE_EXTERNAL-- /path/to/missing.php for PHPT file',
            $exception->getMessage(),
        );
    }

    public function testUnsupportedPhptSectionExceptionMessage(): void
    {
        $exception = new UnsupportedPhptSectionException('CGI');

        $this->assertSame(
            'PHPUnit does not support PHPT --CGI-- sections',
            $exception->getMessage(),
        );
    }
}
