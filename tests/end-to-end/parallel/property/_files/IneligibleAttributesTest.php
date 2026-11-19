<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelProperty;

use function getenv;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class IneligibleAttributesTest extends TestCase
{
    #[Property(10)]
    #[RunInSeparateProcess]
    public function testIsAnnotatedWithRunInSeparateProcess(int $value): void
    {
        $this->assertIsString(getenv('PHPUNIT_WORKER_ID'));
    }
}
