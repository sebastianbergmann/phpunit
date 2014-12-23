<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    PHPUnit
 * @subpackage Util
 * @author     Henrique Moody <henriquemoody@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 */
class PHPUnit_Util_Printer_Factory
{
    const RESULT_PRINTER = 'PHPUnit_TextUI_ResultPrinter';

    /**
     * @return array
     */
    private function parseOptions(array $options)
    {
        if (isset($options['printerClass']) && ! isset($options['printer'])) {
            $options['printer'] = $options['printerClass'];
        } elseif (!isset($options['printer'])) {
            $options['printer'] = self::RESULT_PRINTER;
        }

        return $options;
    }

    /**
     * @return array
     */
    private function parseDefaultConstructorArguments(array $options)
    {
        $arguments = array();
        $arguments[0] = 'php://stdout';
        if (isset($options['stderr']) && true === $options['stderr']) {
            $arguments[0] = 'php://stderr';
        }

        return $arguments;
    }

    /**
     * Creates a printer based on the given options.
     *
     * @throws PHPUnit_Util_Exception
     * @param  array                  $options
     *
     * @return PHPUnit_Util_Printer
     */
    public function getPrinter(array $options)
    {
        $parsedOptions = $this->parseOptions($options);

        if ($parsedOptions['printer'] instanceof PHPUnit_Util_Printer) {
            return $parsedOptions['printer'];
        }

        $constructorArguments = $this->parseDefaultConstructorArguments($parsedOptions);

        if (self::RESULT_PRINTER === $parsedOptions['printer']) {
            return $this->getResultPrinter($parsedOptions, $constructorArguments);
        }

        return $this->getGenericPrinter($parsedOptions, $constructorArguments);
    }

    /**
     * @throws PHPUnit_Util_Exception
     * @param  array                  $options
     * @param  array                  $constructorArguments
     *
     * @return PHPUnit_TextUI_ResultPrinter
     */
    private function getResultPrinter(array $options, array $constructorArguments)
    {
        $keys = array('verbose', 'colors', 'debug', 'columns');
        foreach ($keys as $key) {
            if (!array_key_exists($key, $options)) {
                break;
            }

            $constructorArguments[] = $options[$key];
        }

        return $this->getGenericPrinter($options, $constructorArguments);
    }

    /**
     * @throws PHPUnit_Util_Exception
     * @param  array                  $options
     * @param  array                  $constructorArguments
     *
     * @return PHPUnit_Util_Printer
     */
    private function getGenericPrinter(array $options, array $constructorArguments)
    {
        $this->loadPrinterFile($options);

        if (!class_exists($options['printer'])) {
            throw new PHPUnit_Util_Exception(sprintf('"%s" was not found', $options['printer']));
        }

        $reflection = new ReflectionClass($options['printer']);
        if (!$reflection->isSubclassOf('PHPUnit_Util_Printer')) {
            throw new PHPUnit_Util_Exception(sprintf('"%s" is not a valid printer', $options['printer']));
        }

        return $reflection->newInstanceArgs($constructorArguments);
    }

    /**
     * @throws PHPUnit_Util_Exception
     * @param  array                  $options
     *
     * @return null
     */
    private function loadPrinterFile(array $options)
    {
        if (class_exists($options['printer'], false)) {
            return;
        }

        if (!isset($options['printerFile'])) {
            return;
        }

        if (empty($options['printerFile'])) {
            $options['printerFile'] = PHPUnit_Util_Filesystem::classNameToFilename($options['printer']);
        }

        $filename = stream_resolve_include_path($options['printerFile']);
        if (false === $filename) {
            throw new PHPUnit_Util_Exception(sprintf('Could not load "%s"', $options['printerFile']));
        }

        require $filename;
    }
}
