<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestImpactData;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;

abstract class TestCaseThatRegistersAFixture extends TestCase
{
    #[After]
    protected function registerWhatTheTestUsed(): void
    {
        $this->registerFixture(__DIR__ . '/../fixture-directory');
    }
}
