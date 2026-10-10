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

use function gc_collect_cycles;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;
use WeakReference;

final class FalsifyingExampleIsReleasedWhileItsCounterexampleIsShrunkTest extends TestCase
{
    /**
     * @var ?WeakReference<self>
     */
    private static ?WeakReference $firstFalsifyingExample = null;

    /**
     * @var ?WeakReference<self>
     */
    private static ?WeakReference $firstExample = null;

    #[Property]
    public function testFalsifiedByValuesGreaterThanTen(int $value): void
    {
        gc_collect_cycles();

        $this->assertNull(self::$firstFalsifyingExample?->get(), 'the example that first falsified the property is still alive');

        if ($value > 10) {
            self::$firstFalsifyingExample ??= WeakReference::create($this);

            $this->fail('falsified');
        }
    }

    #[Property]
    public function testFalsifiedByEveryValue(int $value): void
    {
        gc_collect_cycles();

        $this->assertNull(self::$firstExample?->get(), 'the first example is still alive');

        self::$firstExample ??= WeakReference::create($this);

        $this->fail('falsified');
    }
}
