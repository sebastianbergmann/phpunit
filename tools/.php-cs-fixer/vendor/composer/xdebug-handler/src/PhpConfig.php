<?php

declare(strict_types=1);

/*
 * This file is part of composer/xdebug-handler.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Composer\XdebugHandler;

/**
 * @author John Stevenson <john-stevenson@blueyonder.co.uk>
 *
 * @phpstan-type restartData array{tmpIni: string, scannedInis: bool, scanDir: false|string, phprc: false|string, inis: string[], skipped: string}
 */
class PhpConfig
{
    /**
     * Use the original PHP configuration
     *
     * @return string[] Empty array of PHP cli options
     */
    public function useOriginal(): array
    {
        $this->getDataAndReset();
        return [];
    }

    /**
     * Use standard restart settings
     *
     * @return string[] PHP cli options
     */
    public function useStandard(): array
    {
        $data = $this->getDataAndReset();
        if ($data !== null) {
            return ['-n', '-c', $data['tmpIni']];
        }

        return [];
    }

    /**
     * Use environment variables to persist settings
     *
     * @return string[] Empty array of PHP cli options
     */
    public function usePersistent(): array
    {
        $data = $this->getDataAndReset();
        if ($data !== null) {
            $this->updateEnv('PHPRC', $data['tmpIni']);
            $this->updateEnv('PHP_INI_SCAN_DIR', '');
        }

        return [];
    }

    /**
     * Returns restart data if available and resets the environment
     *
     * @phpstan-return restartData|null
     */
    private function getDataAndReset(): ?array
    {
        $data = XdebugHandler::getRestartSettings();
        if ($data !== null) {
            $this->updateEnv('PHPRC', $data['phprc']);
            $this->updateEnv('PHP_INI_SCAN_DIR', $data['scanDir']);
        }

        return $data;
    }

    /**
     * Updates a restart settings value in the environment
     *
     * @param string $name
     * @param string|false $value
     */
    private function updateEnv(string $name, $value): void
    {
        Process::setEnv($name, false !== $value ? $value : null);
    }
}
