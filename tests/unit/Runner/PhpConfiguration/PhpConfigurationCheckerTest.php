<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\PhpConfiguration;

use function array_merge;
use function assert;
use function extension_loaded;
use function ini_get;
use function ini_set;
use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpConfigurationChecker::class)]
#[UsesClass(PhpConfigurationCheckResult::class)]
#[Small]
#[Group('test-runner')]
final class PhpConfigurationCheckerTest extends TestCase
{
    private string $displayErrors;

    protected function setUp(): void
    {
        $displayErrors = ini_get('display_errors');

        assert($displayErrors !== false);

        $this->displayErrors = $displayErrors;
    }

    protected function tearDown(): void
    {
        ini_set('display_errors', $this->displayErrors);
    }

    public function testChecksConfigurationSettingsRelevantForPhpunit(): void
    {
        $expected = [
            'display_errors',
            'display_startup_errors',
            'error_reporting',
        ];

        if (extension_loaded('xdebug')) {
            $expected[] = 'xdebug.show_exception_trace';
        }

        $expected = array_merge(
            $expected,
            [
                'zend.assertions',
                'assert.exception',
                'memory_limit',
            ],
        );

        $names = [];

        foreach ((new PhpConfigurationChecker)->check() as $result) {
            $names[] = $result->name();
        }

        $this->assertSame($expected, $names);
    }

    public function testSettingWithExpectedValueIsOk(): void
    {
        ini_set('display_errors', '1');

        $result = $this->resultFor('display_errors');

        $this->assertTrue($result->isOk());
        $this->assertSame('1', $result->actualValue());
        $this->assertSame('On', $result->valueForConfiguration());
    }

    public function testSettingWithUnexpectedValueIsNotOk(): void
    {
        ini_set('display_errors', '0');

        $result = $this->resultFor('display_errors');

        $this->assertFalse($result->isOk());
        $this->assertSame('0', $result->actualValue());
    }

    /**
     * @param non-empty-string $name
     */
    private function resultFor(string $name): PhpConfigurationCheckResult
    {
        foreach ((new PhpConfigurationChecker)->check() as $result) {
            if ($result->name() === $name) {
                return $result;
            }
        }

        $this->fail(
            sprintf(
                'PHP configuration setting %s was not checked',
                $name,
            ),
        );
    }
}
