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

use function file_exists;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Renderer::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/phpt')]
final class RendererTest extends TestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }

    public function testRenderReplacesFileAndDirConstants(): void
    {
        $renderer = new Renderer;

        $result = $renderer->render(
            '/path/to/test.phpt',
            '<?php echo __DIR__; echo __FILE__;',
        );

        $this->assertSame(
            "<?php echo '/path/to'; echo '/path/to/test.phpt';",
            $result,
        );
    }

    public function testRenderForCoverageWritesJobFileAndUpdatesJobVariable(): void
    {
        $files = $this->createTempFiles();
        $job   = '<?php echo 1;';

        (new Renderer)->renderForCoverage(
            $job,
            false,
            null,
            '',
            $files,
        );

        $this->assertStringEqualsFile($files['job'], '<?php echo 1;');
        $this->assertStringContainsString('forLineCoverage', $job);
        $this->assertStringContainsString($files['coverage'], $job);
        $this->assertStringContainsString('if (null)', $job);
    }

    public function testRenderForCoverageWithPathCoverage(): void
    {
        $files = $this->createTempFiles();
        $job   = '<?php echo 1;';

        (new Renderer)->renderForCoverage(
            $job,
            true,
            null,
            '',
            $files,
        );

        $this->assertStringContainsString('forLineAndPathCoverage', $job);
    }

    public function testRenderForCoverageWithCacheDirectory(): void
    {
        $files = $this->createTempFiles();
        $job   = '<?php echo 1;';

        (new Renderer)->renderForCoverage(
            $job,
            false,
            '/tmp/cache',
            '',
            $files,
        );

        $this->assertStringContainsString("if ('/tmp/cache')", $job);
    }

    public function testRenderForCoverageWithBootstrap(): void
    {
        $files = $this->createTempFiles();
        $job   = '<?php echo 1;';

        (new Renderer)->renderForCoverage(
            $job,
            false,
            null,
            '/path/to/bootstrap.php',
            $files,
        );

        $this->assertStringContainsString("'/path/to/bootstrap.php'", $job);
    }

    /**
     * @return array{coverage: non-empty-string, job: non-empty-string}
     */
    private function createTempFiles(): array
    {
        $jobFile      = tempnam(sys_get_temp_dir(), 'phpt_job_');
        $coverageFile = tempnam(sys_get_temp_dir(), 'phpt_cov_');

        $this->tempFiles[] = $jobFile;
        $this->tempFiles[] = $coverageFile;

        return [
            'coverage' => $coverageFile,
            'job'      => $jobFile,
        ];
    }
}
