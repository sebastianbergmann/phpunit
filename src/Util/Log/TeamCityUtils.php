<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\Log;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Util\Filter;
use PHPUnit\Framework\Test;
use ReflectionClass;

trait TeamCityUtils
{
    protected function getFlowId()
    {
        if (stripos(ini_get('disable_functions'), 'getmypid') !== false) {
            return false;
        }

        return getmypid();
    }

    /**
     * @param Exception $e
     *
     * @return string
     */
    protected function getMessage(Exception $e)
    {
        $message = '';

        if (!$e instanceof Exception) {
            if (strlen(get_class($e)) != 0) {
                $message = $message . get_class($e);
            }

            if (strlen($message) != 0 && strlen($e->getMessage()) != 0) {
                $message = $message . ' : ';
            }
        }

        return $message . $e->getMessage();
    }

    /**
     * @param Exception $e
     *
     * @return string
     */
    protected function getDetails(Exception $e)
    {
        $stackTrace = Filter::getFilteredStacktrace($e);
        $previous = $e->getPrevious();

        while ($previous) {
            $stackTrace .= "\nCaused by\n" .
            TestFailure::exceptionToString($previous) . "\n" .
            Filter::getFilteredStacktrace($previous);

            $previous = $previous->getPrevious();
        }

        return ' ' . str_replace("\n", "\n ", $stackTrace);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function getPrimitiveValueAsString($value)
    {
        if (is_null($value)) {
            return 'null';
        } elseif (is_bool($value)) {
            return $value == true ? 'true' : 'false';
        } elseif (is_scalar($value)) {
            return print_r($value, true);
        }
    }

    /**
     * @param  $text
     *
     * @return string
     */
    protected function escapeValue($text)
    {
        $text = str_replace('|', '||', $text);
        $text = str_replace("'", "|'", $text);
        $text = str_replace("\n", '|n', $text);
        $text = str_replace("\r", '|r', $text);
        $text = str_replace(']', '|]', $text);
        $text = str_replace('[', '|[', $text);

        return $text;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    protected function getFileName($className)
    {
        $reflectionClass = new ReflectionClass($className);
        $fileName = $reflectionClass->getFileName();

        return $fileName;
    }

    public function printIgnoredTest($testName, Exception $e)
    {
        $this->printEvent(
            'testIgnored',
            [
                'name' => $testName,
                'message' => self::getMessage($e),
                'details' => self::getDetails($e),
            ]
        );
    }

    /**
     * @param string $eventName
     * @param array  $params
     */
    protected function printEvent($eventName, $params = [])
    {
        $this->write("\n##teamcity[$eventName");

        if ($flowId = $this->getFlowId()) {
            $params['flowId'] = $flowId;
        }

        foreach ($params as $key => $value) {
            $escapedValue = self::escapeValue($value);
            $this->write(" $key='$escapedValue'");
        }

        $this->write("]\n");
    }

    /**
     * @param Test                            $test
     * @param \Exception $e
     */
    protected function testFailed(Test $test, \Exception $e): void
    {
        $parameters = [
            'name' => $test->getName(),
            'message' => $this->getMessage($e),
            'details' => $this->getDetails($e),
        ];

        if ($e instanceof ExpectationFailedException) {
            $comparisonFailure = $e->getComparisonFailure();

            if ($comparisonFailure instanceof ComparisonFailure) {
                $expectedString = $comparisonFailure->getExpectedAsString();

                if (is_null($expectedString) || empty($expectedString)) {
                    $expectedString = self::getPrimitiveValueAsString($comparisonFailure->getExpected());
                }

                $actualString = $comparisonFailure->getActualAsString();

                if (is_null($actualString) || empty($actualString)) {
                    $actualString = self::getPrimitiveValueAsString($comparisonFailure->getActual());
                }

                if (!is_null($actualString) && !is_null($expectedString)) {
                    $parameters['type'] = 'comparisonFailure';
                    $parameters['actual'] = $actualString;
                    $parameters['expected'] = $expectedString;
                }
            }
        }

        $this->printEvent('testFailed', $parameters);
    }
}
