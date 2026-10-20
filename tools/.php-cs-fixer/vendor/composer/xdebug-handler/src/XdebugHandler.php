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

use Composer\Pcre\Preg;
use Psr\Log\LoggerInterface;

/**
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 *
 * @phpstan-import-type restartData from PhpConfig
 */
class XdebugHandler
{
    const SUFFIX_ALLOW = '_ALLOW_XDEBUG';
    const SUFFIX_INIS = '_ORIGINAL_INIS';
    const RESTART_ID = 'internal';
    const RESTART_SETTINGS = 'XDEBUG_HANDLER_SETTINGS';
    const DEBUG = 'XDEBUG_HANDLER_DEBUG';

    /** @var string|null */
    protected $tmpIni;

    /** @var bool */
    private static $inRestart;

    /** @var string */
    private static $name;

    /** @var string|null */
    private static $skipped;

    /** @var bool */
    private static $xdebugActive;

    /** @var string|null */
    private static $xdebugMode;

    /** @var string|null */
    private static $xdebugVersion;

    /** @var bool */
    private $cli;

    /** @var string|null */
    private $debug;

    /** @var string */
    private $envAllowXdebug;

    /** @var string */
    private $envOriginalInis;

    /** @var bool */
    private $persistent;

    /** @var string|null */
    private $script;

    /** @var Status */
    private $statusWriter;

    /**
     * Constructor
     *
     * The $envPrefix is used to create distinct environment variables. It is
     * uppercased and prepended to the default base values. For example 'myapp'
     * would result in MYAPP_ALLOW_XDEBUG and MYAPP_ORIGINAL_INIS.
     *
     * @param string $envPrefix Value used in environment variables
     * @throws \RuntimeException If the parameter is invalid
     */
    public function __construct(string $envPrefix)
    {
        if ($envPrefix === '') {
            throw new \RuntimeException('Invalid constructor parameter');
        }

        self::$name = strtoupper($envPrefix);
        $this->envAllowXdebug = self::$name.self::SUFFIX_ALLOW;
        $this->envOriginalInis = self::$name.self::SUFFIX_INIS;

        self::setXdebugDetails();
        self::$inRestart = false;

        if ($this->cli = PHP_SAPI === 'cli') {
            $this->debug = (string) getenv(self::DEBUG);
        }

        $this->statusWriter = new Status($this->envAllowXdebug, (bool) $this->debug);
    }

    /**
     * Activates status message output to a PSR3 logger
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->statusWriter->setLogger($logger);
        return $this;
    }

    /**
     * Sets the main script location if it cannot be called from argv
     */
    public function setMainScript(string $script): self
    {
        $this->script = $script;
        return $this;
    }

    /**
     * Persist the settings to keep Xdebug out of sub-processes
     */
    public function setPersistent(): self
    {
        $this->persistent = true;
        return $this;
    }

    /**
     * Checks if Xdebug is loaded and the process needs to be restarted
     *
     * This behaviour can be disabled by setting the MYAPP_ALLOW_XDEBUG
     * environment variable to 1. This variable is used internally so that
     * the restarted process is created only once.
     */
    public function check(): void
    {
        $this->notify(Status::CHECK, self::$xdebugVersion.'|'.self::$xdebugMode);
        $envArgs = explode('|', (string) getenv($this->envAllowXdebug));

        if (!((bool) $envArgs[0]) && $this->requiresRestart(self::$xdebugActive)) {
            // Restart required
            $this->notify(Status::RESTART);
            $command = $this->prepareRestart();

            if ($command !== null) {
                $this->restart($command);
            }
            return;
        }

        if (self::RESTART_ID === $envArgs[0] && count($envArgs) === 5) {
            // Restarted, so unset environment variable and use saved values
            $this->notify(Status::RESTARTED);

            Process::setEnv($this->envAllowXdebug);
            self::$inRestart = true;

            if (self::$xdebugVersion === null) {
                // Skipped version is only set if Xdebug is not loaded
                self::$skipped = $envArgs[1];
            }

            $this->tryEnableSignals();

            // Put restart settings in the environment
            $this->setEnvRestartSettings($envArgs);
            return;
        }

        $this->notify(Status::NORESTART);
        $settings = self::getRestartSettings();

        if ($settings !== null) {
            // Called with existing settings, so sync our settings
            $this->syncSettings($settings);
        }
    }

    /**
     * Returns an array of php.ini locations with at least one entry
     *
     * The equivalent of calling php_ini_loaded_file then php_ini_scanned_files.
     * The loaded ini location is the first entry and may be an empty string.
     *
     * @return non-empty-list<string>
     */
    public static function getAllIniFiles(): array
    {
        if (self::$name !== null) {
            $env = getenv(self::$name.self::SUFFIX_INIS);

            if (false !== $env) {
                return explode(PATH_SEPARATOR, $env);
            }
        }

        $paths = [(string) php_ini_loaded_file()];
        $scanned = php_ini_scanned_files();

        if ($scanned !== false) {
            $paths = array_merge($paths, array_map('trim', explode(',', $scanned)));
        }

        return $paths;
    }

    /**
     * Returns an array of restart settings or null
     *
     * Settings will be available if the current process was restarted, or
     * called with the settings from an existing restart.
     *
     * @phpstan-return restartData|null
     */
    public static function getRestartSettings(): ?array
    {
        $envArgs = explode('|', (string) getenv(self::RESTART_SETTINGS));

        if (count($envArgs) !== 6
            || (!self::$inRestart && php_ini_loaded_file() !== $envArgs[0])) {
            return null;
        }

        return [
            'tmpIni' => $envArgs[0],
            'scannedInis' => (bool) $envArgs[1],
            'scanDir' => '*' === $envArgs[2] ? false : $envArgs[2],
            'phprc' => '*' === $envArgs[3] ? false : $envArgs[3],
            'inis' => explode(PATH_SEPARATOR, $envArgs[4]),
            'skipped' => $envArgs[5],
        ];
    }

    /**
     * Returns the Xdebug version that triggered a successful restart
     */
    public static function getSkippedVersion(): string
    {
        return (string) self::$skipped;
    }

    /**
     * Returns whether Xdebug is loaded and active
     *
     * true: if Xdebug is loaded and is running in an active mode.
     * false: if Xdebug is not loaded, or it is running with xdebug.mode=off.
     */
    public static function isXdebugActive(): bool
    {
        self::setXdebugDetails();
        return self::$xdebugActive;
    }

    /**
     * Allows an extending class to decide if there should be a restart
     *
     * The default is to restart if Xdebug is loaded and its mode is not "off".
     */
    protected function requiresRestart(bool $default): bool
    {
        return $default;
    }

    /**
     * Allows an extending class to access the tmpIni
     *
     * @param non-empty-list<string> $command
     */
    protected function restart(array $command): void
    {
        $this->doRestart($command);
    }

    /**
     * Executes the restarted command then deletes the tmp ini
     *
     * @param non-empty-list<string> $command
     * @phpstan-return never
     */
    private function doRestart(array $command): void
    {
        if (PHP_VERSION_ID >= 70400) {
            $cmd = $command;
            $displayCmd = sprintf('[%s]', implode(', ', $cmd));
        } else {
            $cmd = Process::escapeShellCommand($command);
            if (defined('PHP_WINDOWS_VERSION_BUILD')) {
                // Outer quotes required on cmd string below PHP 8
                $cmd = '"'.$cmd.'"';
            }
            $displayCmd = $cmd;
        }

        $this->tryEnableSignals();
        $this->notify(Status::RESTARTING, $displayCmd);

        $process = proc_open($cmd, [], $pipes);
        if (is_resource($process)) {
            $exitCode = proc_close($process);
        }

        if (!isset($exitCode)) {
            // Unlikely that php or the default shell cannot be invoked
            $this->notify(Status::ERROR, 'Unable to restart process');
            $exitCode = -1;
        } else {
            $this->notify(Status::INFO, 'Restarted process exited '.$exitCode);
        }

        if ($this->debug === '2') {
            $this->notify(Status::INFO, 'Temp ini saved: '.$this->tmpIni);
        } else {
            @unlink((string) $this->tmpIni);
        }

        exit($exitCode);
    }

    /**
     * Returns the command line array if everything was written for the restart
     *
     * If any of the following fails (however unlikely) we must return false to
     * stop potential recursion:
     *   - tmp ini file creation
     *   - environment variable creation
     *
     * @return non-empty-list<string>|null
     */
    private function prepareRestart(): ?array
    {
        if (!$this->cli) {
            $this->notify(Status::ERROR, 'Unsupported SAPI: '.PHP_SAPI);
            return null;
        }

        if (($argv = $this->checkServerArgv()) === null) {
            $this->notify(Status::ERROR, '$_SERVER[argv] is not as expected');
            return null;
        }

        if (!$this->checkConfiguration($info)) {
            $this->notify(Status::ERROR, $info);
            return null;
        }

        $mainScript = (string) $this->script;
        if (!$this->checkMainScript($mainScript, $argv)) {
            $this->notify(Status::ERROR, 'Unable to access main script: '.$mainScript);
            return null;
        }

        $tmpDir = sys_get_temp_dir();
        $iniError = 'Unable to create temp ini file at: '.$tmpDir;

        if (($tmpfile = @tempnam($tmpDir, '')) === false) {
            $this->notify(Status::ERROR, $iniError);
            return null;
        }

        $error = null;
        $iniFiles = self::getAllIniFiles();
        $scannedInis = count($iniFiles) > 1;

        if (!$this->writeTmpIni($tmpfile, $iniFiles, $error)) {
            $this->notify(Status::ERROR, $error ?? $iniError);
            @unlink($tmpfile);
            return null;
        }

        if (!$this->setEnvironment($scannedInis, $iniFiles, $tmpfile)) {
            $this->notify(Status::ERROR, 'Unable to set environment variables');
            @unlink($tmpfile);
            return null;
        }

        $this->tmpIni = $tmpfile;

        return $this->getCommand($argv, $tmpfile, $mainScript);
    }

    /**
     * Returns true if the tmp ini file was written
     *
     * @param non-empty-list<string> $iniFiles All ini files used in the current process
     */
    private function writeTmpIni(string $tmpFile, array $iniFiles, ?string &$error): bool
    {
        // $iniFiles has at least one item and it may be empty
        if ($iniFiles[0] === '') {
            array_shift($iniFiles);
        }

        $content = '';
        $sectionRegex = '/^\s*\[(?:PATH|HOST)\s*=/mi';
        $xdebugRegex = '/^\s*(zend_extension\s*=.*xdebug.*)$/mi';

        foreach ($iniFiles as $file) {
            // Check for inaccessible ini files
            if (($data = @file_get_contents($file)) === false) {
                $error = 'Unable to read ini: '.$file;
                return false;
            }
            // Check and remove directives after HOST and PATH sections
            if (Preg::isMatchWithOffsets($sectionRegex, $data, $matches)) {
                $data = substr($data, 0, $matches[0][1]);
            }
            $content .= Preg::replace($xdebugRegex, ';$1', $data).PHP_EOL;
        }

        // Merge loaded settings into our ini content, if it is valid
        $config = parse_ini_string($content);
        $loaded = ini_get_all(null, false);

        if (false === $config || false === $loaded) {
            $error = 'Unable to parse ini data';
            return false;
        }

        $content .= $this->mergeLoadedConfig($loaded, $config);

        // Work-around for https://bugs.php.net/bug.php?id=75932
        $content .= 'opcache.enable_cli=0'.PHP_EOL;

        return (bool) @file_put_contents($tmpFile, $content);
    }

    /**
     * Returns the command line arguments for the restart
     *
     * @param non-empty-list<string> $argv
     * @return non-empty-list<string>
     */
    private function getCommand(array $argv, string $tmpIni, string $mainScript): array
    {
        $php = [PHP_BINARY];
        $args = array_slice($argv, 1);

        if (!$this->persistent) {
            // Use command-line options
            array_push($php, '-n', '-c', $tmpIni);
        }

        return array_merge($php, [$mainScript], $args);
    }

    /**
     * Returns true if the restart environment variables were set
     *
     * No need to update $_SERVER since this is set in the restarted process.
     *
     * @param non-empty-list<string> $iniFiles All ini files used in the current process
     */
    private function setEnvironment(bool $scannedInis, array $iniFiles, string $tmpIni): bool
    {
        $scanDir = getenv('PHP_INI_SCAN_DIR');
        $phprc = getenv('PHPRC');

        // Make original inis available to restarted process
        if (!putenv($this->envOriginalInis.'='.implode(PATH_SEPARATOR, $iniFiles))) {
            return false;
        }

        if ($this->persistent) {
            // Use the environment to persist the settings
            if (!putenv('PHP_INI_SCAN_DIR=') || !putenv('PHPRC='.$tmpIni)) {
                return false;
            }
        }

        // Flag restarted process and save values for it to use
        $envArgs = [
            self::RESTART_ID,
            self::$xdebugVersion,
            (int) $scannedInis,
            false === $scanDir ? '*' : $scanDir,
            false === $phprc ? '*' : $phprc,
        ];

        return putenv($this->envAllowXdebug.'='.implode('|', $envArgs));
    }

    /**
     * Logs status messages
     */
    private function notify(string $op, ?string $data = null): void
    {
        $this->statusWriter->report($op, $data);
    }

    /**
     * Returns default, changed and command-line ini settings
     *
     * @param mixed[] $loadedConfig All current ini settings
     * @param mixed[] $iniConfig Settings from user ini files
     *
     */
    private function mergeLoadedConfig(array $loadedConfig, array $iniConfig): string
    {
        $content = '';

        foreach ($loadedConfig as $name => $value) {
            // Value will either be null, string or array (HHVM only)
            if (!is_string($value)
                || strpos($name, 'xdebug') === 0
                || $name === 'apc.mmap_file_mask') {
                continue;
            }

            if (!isset($iniConfig[$name]) || $iniConfig[$name] !== $value) {
                // Double-quote escape each value
                $content .= $name.'="'.addcslashes($value, '\\"').'"'.PHP_EOL;
            }
        }

        return $content;
    }

    /**
     * Returns true if the script name can be used
     *
     * @param non-empty-list<string> $argv
     */
    private function checkMainScript(string &$mainScript, array $argv): bool
    {
        if ($mainScript !== '') {
            // Allow an application to set -- for standard input
            return file_exists($mainScript) || '--' === $mainScript;
        }

        if (file_exists($mainScript = $argv[0])) {
            return true;
        }

        // Use a backtrace to resolve Phar and chdir issues.
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $main = end($trace);

        if ($main !== false && isset($main['file'])) {
            return file_exists($mainScript = $main['file']);
        }

        return false;
    }

    /**
     * Adds restart settings to the environment
     *
     * @param non-empty-list<string> $envArgs
     */
    private function setEnvRestartSettings(array $envArgs): void
    {
        $settings = [
            php_ini_loaded_file(),
            $envArgs[2],
            $envArgs[3],
            $envArgs[4],
            getenv($this->envOriginalInis),
            self::$skipped,
        ];

        Process::setEnv(self::RESTART_SETTINGS, implode('|', $settings));
    }

    /**
     * Syncs settings and the environment if called with existing settings
     *
     * @phpstan-param restartData $settings
     */
    private function syncSettings(array $settings): void
    {
        if (false === getenv($this->envOriginalInis)) {
            // Called by another app, so make original inis available
            Process::setEnv($this->envOriginalInis, implode(PATH_SEPARATOR, $settings['inis']));
        }

        self::$skipped = $settings['skipped'];
        $this->notify(Status::INFO, 'Process called with existing restart settings');
    }

    /**
     * Returns true if there are no known configuration issues
     */
    private function checkConfiguration(?string &$info): bool
    {
        if (!function_exists('proc_open')) {
            $info = 'proc_open function is disabled';
            return false;
        }

        if (!file_exists(PHP_BINARY)) {
            $info = 'PHP_BINARY is not available';
            return false;
        }

        if (extension_loaded('uopz') && !((bool) ini_get('uopz.disable'))) {
            // uopz works at opcode level and disables exit calls
            if (function_exists('uopz_allow_exit')) {
                @uopz_allow_exit(true);
            } else {
                $info = 'uopz extension is not compatible';
                return false;
            }
        }

        // Check UNC paths when using cmd.exe
        if (defined('PHP_WINDOWS_VERSION_BUILD') && PHP_VERSION_ID < 70400) {
            $workingDir = getcwd();

            if ($workingDir === false) {
                $info = 'unable to determine working directory';
                return false;
            }

            if (0 === strpos($workingDir, '\\\\')) {
                $info = 'cmd.exe does not support UNC paths: '.$workingDir;
                return false;
            }
        }

        return true;
    }

    /**
     * Enables async signals and control interrupts in the restarted process
     *
     * Available on Unix PHP 7.1+ with the pcntl extension and Windows PHP 7.4+.
     */
    private function tryEnableSignals(): void
    {
        if (function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
            pcntl_async_signals(true);
            $message = 'Async signals enabled';

            if (!self::$inRestart) {
                // Restarting, so ignore SIGINT in parent
                pcntl_signal(SIGINT, SIG_IGN);
            } elseif (is_int(pcntl_signal_get_handler(SIGINT))) {
                // Restarted, no handler set so force default action
                pcntl_signal(SIGINT, SIG_DFL);
            }
        }

        if (!self::$inRestart && function_exists('sapi_windows_set_ctrl_handler')) {
            // Restarting, so set a handler to ignore CTRL events in the parent.
            // This ensures that CTRL+C events will be available in the child
            // process without having to enable them there, which is unreliable.
            sapi_windows_set_ctrl_handler(function ($evt) {});
        }
    }

    /**
     * Returns $_SERVER['argv'] if it is as expected
     *
     * @return non-empty-list<string>|null
     */
    private function checkServerArgv(): ?array
    {
        $result = [];

        if (isset($_SERVER['argv']) && is_array($_SERVER['argv'])) {
            foreach ($_SERVER['argv'] as $value) {
                if (!is_string($value)) {
                    return null;
                }

                $result[] = $value;
            }
        }

        return count($result) > 0 ? $result : null;
    }

    /**
     * Sets static properties $xdebugActive, $xdebugVersion and $xdebugMode
     */
    private static function setXdebugDetails(): void
    {
        if (self::$xdebugActive !== null) {
            return;
        }

        self::$xdebugActive = false;
        if (!extension_loaded('xdebug')) {
            return;
        }

        $version = phpversion('xdebug');
        self::$xdebugVersion = $version !== false ? $version : 'unknown';

        if (version_compare(self::$xdebugVersion, '3.1', '>=')) {
            $modes = xdebug_info('mode');
            self::$xdebugMode = count($modes) === 0 ? 'off' : implode(',', $modes);
            self::$xdebugActive = self::$xdebugMode !== 'off';
            return;
        }

        // See if xdebug.mode is supported in this version
        $iniMode = ini_get('xdebug.mode');
        if ($iniMode === false) {
            self::$xdebugActive = true;
            return;
        }

        // Environment value wins but cannot be empty
        $envMode = (string) getenv('XDEBUG_MODE');
        if ($envMode !== '') {
            self::$xdebugMode = $envMode;
        } else {
            self::$xdebugMode = $iniMode !== '' ? $iniMode : 'off';
        }

        // An empty comma-separated list is treated as mode 'off'
        if (Preg::isMatch('/^,+$/', str_replace(' ', '', self::$xdebugMode))) {
            self::$xdebugMode = 'off';
        }

        self::$xdebugActive = self::$xdebugMode !== 'off';
    }
}
