<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Run;

use PHPUnit\Event\Event;
use PHPUnit\Event\Type;

final class BeforeRun implements Event
{
    private Run $run;

    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    public function type(): Type
    {
        return new BeforeRunType();
    }

    public function run(): Run
    {
        return $this->run;
    }
}
