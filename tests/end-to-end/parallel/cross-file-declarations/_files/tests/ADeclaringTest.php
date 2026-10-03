<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelCrossFile;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../helpers/SharedDataProvider.php';

const SHARED_CONSTANT = 'shared constant';

function sharedFunction(): string
{
    return 'shared function';
}

interface SharedInterface
{
    public function sharedInterfaceMethod(): string;
}

trait SharedTrait
{
    public function sharedTraitMethod(): string
    {
        return 'shared trait';
    }
}

final class ADeclaringTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
