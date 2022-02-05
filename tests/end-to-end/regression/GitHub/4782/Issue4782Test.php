<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

final class Issue4782Test extends TestCase
{
    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/4782
     */
    public function test4782(): void
    {
        try {
            $this->assertStringNotContainsString(' contains ', ' contains ');
        } catch (AssertionFailedError $e) {
            $this->assertSame(
                'Failed asserting that \' contains \' does not contain " contains ".',
                $e->toString()
            );

            return;
        }
        $this->fail('This test should have thrown an ' . AssertionFailedError::class . ' exception.');
    }
}
