<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use const FILE_APPEND;
use function file_put_contents;
use function getenv;
use function is_string;
use function json_encode;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ReproducibilityTest extends TestCase
{
    /**
     * @param list<int> $values
     */
    #[Property(examples: 20)]
    public function testOne(array $values): void
    {
        $this->log('testOne', $values);

        $this->assertIsArray($values);
    }

    #[Property(examples: 20)]
    public function testTwo(string $value): void
    {
        $this->log('testTwo', $value);

        $this->assertIsString($value);
    }

    private function log(string $property, mixed $value): void
    {
        $file = getenv('PHPUNIT_PROPERTY_EXAMPLES_LOG');

        if (is_string($file)) {
            file_put_contents($file, $property . ': ' . json_encode($value) . "\n", FILE_APPEND);
        }
    }
}
