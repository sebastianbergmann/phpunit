<?php

namespace React\EventLoop\Timer;

use React\EventLoop\TimerInterface;

/**
 * A scheduler implementation that can hold multiple timer instances
 *
 * This class should only be used internally, see TimerInterface instead.
 *
 * @see TimerInterface
 * @internal
 */
final class Timers
{
    private $time;
    private $timers = array();
    private $schedule = array();
    private $sorted = true;
    private $useHighResolution;

    public function __construct()
    {
        // prefer high-resolution timer, available as of PHP 7.3+
        $this->useHighResolution = \function_exists('hrtime');
    }

    public function updateTime()
    {
        return $this->time = $this->useHighResolution ? \hrtime(true) * 1e-9 : \microtime(true);
    }

    public function getTime()
    {
        return $this->time ?: $this->updateTime();
    }

    public function add(TimerInterface $timer)
    {
        $id = \PHP_VERSION_ID < 70200 ? \spl_object_hash($timer) : \spl_object_id($timer);
        $this->timers[$id] = $timer;
        $this->schedule[$id] = $timer->getInterval() + $this->updateTime();
        $this->sorted = false;
    }

    public function contains(TimerInterface $timer)
    {
        $id = \PHP_VERSION_ID < 70200 ? \spl_object_hash($timer) : \spl_object_id($timer);
        return isset($this->timers[$id]);
    }

    public function cancel(TimerInterface $timer)
    {
        $id = \PHP_VERSION_ID < 70200 ? \spl_object_hash($timer) : \spl_object_id($timer);
        unset($this->timers[$id], $this->schedule[$id]);
    }

    public function getFirst()
    {
        // ensure timers are sorted to simply accessing next (first) one
        if (!$this->sorted) {
            $this->sorted = true;
            \asort($this->schedule);
        }

        return \reset($this->schedule);
    }

    public function isEmpty()
    {
        return \count($this->timers) === 0;
    }

    public function tick()
    {
        // hot path: skip timers if nothing is scheduled
        if (!$this->schedule) {
            return;
        }

        // ensure timers are sorted so we can execute in order
        if (!$this->sorted) {
            $this->sorted = true;
            \asort($this->schedule);
        }

        $time = $this->updateTime();

        foreach ($this->schedule as $id => $scheduled) {
            // schedule is ordered, so loop until first timer that is not scheduled for execution now
            if ($scheduled >= $time) {
                break;
            }

            // skip any timers that are removed while we process the current schedule
            if (!isset($this->schedule[$id]) || $this->schedule[$id] !== $scheduled) {
                continue;
            }

            $timer = $this->timers[$id];
            \call_user_func($timer->getCallback(), $timer);

            // re-schedule if this is a periodic timer and it has not been cancelled explicitly already
            if ($timer->isPeriodic() && isset($this->timers[$id])) {
                $this->schedule[$id] = $timer->getInterval() + $time;
                $this->sorted = false;
            } else {
                unset($this->timers[$id], $this->schedule[$id]);
            }
        }
    }
}
