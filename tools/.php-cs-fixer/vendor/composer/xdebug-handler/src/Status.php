<?php

/*
 * This file is part of composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Composer\XdebugHandler;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 * @internal
 */
class Status
{
    const ENV_RESTART = 'XDEBUG_HANDLER_RESTART';
    const CHECK = 'Check';
    const ERROR = 'Error';
    const INFO = 'Info';
    const NORESTART = 'NoRestart';
    const RESTART = 'Restart';
    const RESTARTING = 'Restarting';
    const RESTARTED = 'Restarted';

    /** @var bool */
    private $debug;

    /** @var string */
    private $envAllowXdebug;

    /** @var string|null */
    private $loaded;

    /** @var LoggerInterface|null */
    private $logger;

    /** @var bool */
    private $modeOff;

     /** @var float */
    private $time;

    /**
     * @param string $envAllowXdebug Prefixed _ALLOW_XDEBUG name
     * @param bool $debug Whether debug output is required
     */
    public function __construct(string $envAllowXdebug, bool $debug)
    {
        $start = getenv(self::ENV_RESTART);
        Process::setEnv(self::ENV_RESTART);
        $this->time = is_numeric($start) ? round((microtime(true) - $start) * 1000) : 0;

        $this->envAllowXdebug = $envAllowXdebug;
        $this->debug = $debug && defined('STDERR');
        $this->modeOff = false;
    }

    /**
     * Activates status message output to a PSR3 logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Calls a handler method to report a message
     *
     * @throws \InvalidArgumentException If $op is not known
     */
    public function report(string $op, ?string $data): void
    {
        if ($this->logger !== null || $this->debug) {
            $param = (string) $data;

            switch($op) {
                case self::CHECK:
                    $this->reportCheck($param);
                    break;
                case self::ERROR:
                    $this->reportError($param);
                    break;
                case self::INFO:
                    $this->reportInfo($param);
                    break;
                case self::NORESTART:
                    $this->reportNoRestart();
                    break;
                case self::RESTART:
                    $this->reportRestart();
                    break;
                case self::RESTARTED:
                    $this->reportRestarted();
                    break;
                case self::RESTARTING:
                    $this->reportRestarting($param);
                    break;
                default:
                    throw new \InvalidArgumentException('Unknown op handler: '.$op);
            }
        }
    }

    /**
     * Outputs a status message
     */
    private function output(string $text, ?string $level = null): void
    {
        if ($this->logger !== null) {
            $this->logger->log($level !== null ? $level: LogLevel::DEBUG, $text);
        }

        if ($this->debug) {
            fwrite(STDERR, sprintf('xdebug-handler[%d] %s', getmypid(), $text.PHP_EOL));
        }
    }

    /**
     * Checking status message
     */
    private function reportCheck(string $loaded): void
    {
        list($version, $mode) = explode('|', $loaded);

        if ($version !== '') {
            $this->loaded = '('.$version.')'.($mode !== '' ? ' xdebug.mode='.$mode : '');
        }
        $this->modeOff = $mode === 'off';
        $this->output('Checking '.$this->envAllowXdebug);
    }

    /**
     * Error status message
     */
    private function reportError(string $error): void
    {
        $this->output(sprintf('No restart (%s)', $error), LogLevel::WARNING);
    }

    /**
     * Info status message
     */
    private function reportInfo(string $info): void
    {
        $this->output($info);
    }

    /**
     * No restart status message
     */
    private function reportNoRestart(): void
    {
        $this->output($this->getLoadedMessage());

        if ($this->loaded !== null) {
            $text = sprintf('No restart (%s)', $this->getEnvAllow());
            if (!((bool) getenv($this->envAllowXdebug))) {
                $text .= ' Allowed by '.($this->modeOff ? 'xdebug.mode' : 'application');
            }
            $this->output($text);
        }
    }

    /**
     * Restart status message
     */
    private function reportRestart(): void
    {
        $this->output($this->getLoadedMessage());
        Process::setEnv(self::ENV_RESTART, (string) microtime(true));
    }

    /**
     * Restarted status message
     */
    private function reportRestarted(): void
    {
        $loaded = $this->getLoadedMessage();
        $text = sprintf('Restarted (%d ms). %s', $this->time, $loaded);
        $level = $this->loaded !== null ? LogLevel::WARNING : null;
        $this->output($text, $level);
    }

    /**
     * Restarting status message
     */
    private function reportRestarting(string $command): void
    {
        $text = sprintf('Process restarting (%s)', $this->getEnvAllow());
        $this->output($text);
        $text = 'Running: '.$command;
        $this->output($text);
    }

    /**
     * Returns the _ALLOW_XDEBUG environment variable as name=value
     */
    private function getEnvAllow(): string
    {
        return $this->envAllowXdebug.'='.getenv($this->envAllowXdebug);
    }

    /**
     * Returns the Xdebug status and version
     */
    private function getLoadedMessage(): string
    {
        $loaded = $this->loaded !== null ? sprintf('loaded %s', $this->loaded) : 'not loaded';
        return 'The Xdebug extension is '.$loaded;
    }
}
