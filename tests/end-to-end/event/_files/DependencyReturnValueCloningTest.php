<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DependencyReturnValueCloningTest extends TestCase
{
    public function testProducer(): stdClass
    {
        $obj        = new stdClass;
        $obj->value = 'original';

        $this->assertTrue(true);

        return $obj;
    }

    #[DependsUsingDeepClone('testProducer')]
    public function testDeepCloneConsumer(stdClass $obj): void
    {
        $this->assertSame('original', $obj->value);
    }

    #[DependsUsingShallowClone('testProducer')]
    public function testShallowCloneConsumer(stdClass $obj): void
    {
        $this->assertSame('original', $obj->value);
    }

    #[Depends('testProducer')]
    public function testDirectConsumer(stdClass $obj): void
    {
        $this->assertSame('original', $obj->value);
    }
}
