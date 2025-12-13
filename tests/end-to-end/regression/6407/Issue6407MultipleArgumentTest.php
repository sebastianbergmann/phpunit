<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6407;

use PHPUnit\Framework\TestCase;

require __DIR__ . '/src/multiple/Logger.php';

require __DIR__ . '/src/multiple/Service.php';

final class Issue6407MultipleArgumentTest extends TestCase
{
    public function testWithOrderedParametersList(): void
    {
        $logger = $this->createMock(Logger::class);

        $logger
            ->expects($this->exactly(2))
            ->method('log')
            ->withConsecutiveParameterSets(
                ['info', 'Some Info'],
                ['error', 'Some Error'],
            );

        $service = new Service($logger);

        $service->doSomething();
    }

    public function testWithUnOrderedParametersList(): void
    {
        $logger = $this->createMock(Logger::class);

        $logger
            ->expects($this->exactly(2))
            ->method('log')
            ->withParameterSetsInAnyOrder(
                ['error', 'Some Error'],
                ['info', 'Some Info'],
            );

        $service = new Service($logger);

        $service->doSomething();
    }
}
