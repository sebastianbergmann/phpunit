<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DataProviderIssue2922;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @group foo
 */
class FirstTest extends TestCase
{
    /**
     * @dataProvider provide
     */
    public function testFirst($x): void
    {
        $this->assertTrue(true);
    }

    public function provide(): void
    {
        throw new Exception;
    }
}
