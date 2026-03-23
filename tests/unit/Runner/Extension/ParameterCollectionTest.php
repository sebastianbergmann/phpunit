<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\ParameterDoesNotExistException;

#[CoversClass(ParameterCollection::class)]
#[CoversClass(ParameterDoesNotExistException::class)]
#[Small]
#[Group('test-runner')]
final class ParameterCollectionTest extends TestCase
{
    public function testCanBeCreatedFromArray(): void
    {
        $parameters = ParameterCollection::fromArray(['key' => 'value']);

        $this->assertTrue($parameters->has('key'));
    }

    public function testReportsWhetherParameterExists(): void
    {
        $parameters = ParameterCollection::fromArray(['key' => 'value']);

        $this->assertTrue($parameters->has('key'));
        $this->assertFalse($parameters->has('does-not-exist'));
    }

    public function testExistingParameterCanBeRetrieved(): void
    {
        $parameters = ParameterCollection::fromArray(['key' => 'value']);

        $this->assertSame('value', $parameters->get('key'));
    }

    public function testThrowsExceptionWhenRetrievingNonExistingParameter(): void
    {
        $parameters = ParameterCollection::fromArray([]);

        $this->expectException(ParameterDoesNotExistException::class);
        $this->expectExceptionMessage('Parameter "does-not-exist" does not exist');

        $parameters->get('does-not-exist');
    }
}
