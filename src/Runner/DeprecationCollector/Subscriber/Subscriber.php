<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\DeprecationCollector;

/**
 * @template T of Collector|InIsolationCollector
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class Subscriber
{
    /**
     * @var T
     */
    private readonly Collector|InIsolationCollector $collector;

    /**
     * @param T $collector
     */
    public function __construct(Collector|InIsolationCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @return T
     */
    protected function collector(): Collector|InIsolationCollector
    {
        return $this->collector;
    }
}
