<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5891;

use IteratorAggregate;
use PDOException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Traversable;

#[RequiresPhpExtension('pdo')]
final class Issue5965Test extends TestCase
{
    public function testOne(): void
    {
        $exception = new PDOException;
        $reflector = new ReflectionClass($exception);

        $property = $reflector->getProperty('code');
        $property->setValue($exception, 'HY000');

        $this->assertIsString($exception->getCode());

        $iterator = new class($exception) implements IteratorAggregate
        {
            public PDOException $exception;

            public function __construct($exception)
            {
                $this->exception = $exception;
            }

            public function getIterator(): Traversable
            {
                throw $this->exception;
            }
        };

        $this->assertCount(0, $iterator);
    }
}
