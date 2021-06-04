<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue1337Test extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testProvider($a): void
    {
        $this->assertTrue($a);
    }

    public function dataProvider()
    {
        return [
            'c:\\' => [true],
            // The following is commented out because it no longer works in PHP >= 8.1
            //0.9    => [true],
        ];
    }
}
