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

use const E_ALL;
use function assert;
use function extension_loaded;
use function in_array;
use function ini_get;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PhpConfigurationChecker
{
    /**
     * @return non-empty-list<PhpConfigurationCheckResult>
     */
    public function check(): array
    {
        $results = [];

        foreach ($this->settings() as $name => $setting) {
            foreach ($setting['requiredExtensions'] as $extension) {
                if (!extension_loaded($extension)) {
                    // @codeCoverageIgnoreStart
                    continue 2;
                    // @codeCoverageIgnoreEnd
                }
            }

            $actualValue = ini_get($name);

            $actualValueAsString = '';

            if ($actualValue !== false) {
                $actualValueAsString = $actualValue;
            }

            $results[] = new PhpConfigurationCheckResult(
                $name,
                $setting['valueForConfiguration'],
                $actualValueAsString,
                in_array($actualValue, $setting['expectedValues'], true),
            );
        }

        assert($results !== []);

        return $results;
    }

    /**
     * @return non-empty-array<non-empty-string, array{expectedValues: non-empty-list<non-empty-string>, valueForConfiguration: non-empty-string, requiredExtensions: list<non-empty-string>}>
     */
    private function settings(): array
    {
        return [
            'display_errors' => [
                'expectedValues'        => ['1'],
                'valueForConfiguration' => 'On',
                'requiredExtensions'    => [],
            ],
            'display_startup_errors' => [
                'expectedValues'        => ['1'],
                'valueForConfiguration' => 'On',
                'requiredExtensions'    => [],
            ],
            'error_reporting' => [
                'expectedValues'        => ['-1', (string) E_ALL],
                'valueForConfiguration' => '-1',
                'requiredExtensions'    => [],
            ],
            'xdebug.show_exception_trace' => [
                'expectedValues'        => ['0'],
                'valueForConfiguration' => '0',
                'requiredExtensions'    => ['xdebug'],
            ],
            'zend.assertions' => [
                'expectedValues'        => ['1'],
                'valueForConfiguration' => '1',
                'requiredExtensions'    => [],
            ],
            'assert.exception' => [
                'expectedValues'        => ['1'],
                'valueForConfiguration' => '1',
                'requiredExtensions'    => [],
            ],
            'memory_limit' => [
                'expectedValues'        => ['-1'],
                'valueForConfiguration' => '-1',
                'requiredExtensions'    => [],
            ],
        ];
    }
}
