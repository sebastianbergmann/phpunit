<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6486;

use PHPUnit\Framework\Attributes\DataProvider;

trait TheTrait
{
    public function testToMoveDataProviderAttributeToTheLineThatDoesNotExistInTheOtherFile(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('Abracadabra')]
    public function testWithMissingDataProvider(): void
    {
        $this->assertTrue(false);
    }
}
