<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once __DIR__ . '/CoverStub.php';

/**
 * @covers CoverStub
 */
class Coverage extends PHPUnit\Framework\TestCase
{
    public function test_it_should_always_return_true()
    {
        $this->assertTrue(CoverStub::notCoveredWithUse());
    }
}
