<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\AbstractRepeatTestSuite;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @extends AbstractRepeatTestSuite<PhptTestCase>
 */
final readonly class RepeatTestSuite extends AbstractRepeatTestSuite
{
    public function run(): void
    {
        $defectOccurred = false;

        foreach ($this->tests as $test) {
            if ($defectOccurred) {
                EventFacade::emitter()->testSkipped(
                    $this->valueObjectForEvents(),
                    'Test repetition failure',
                );

                continue;
            }

            $test->run();

            if (!$test->passed()) {
                $defectOccurred = true;
            }
        }
    }
}
