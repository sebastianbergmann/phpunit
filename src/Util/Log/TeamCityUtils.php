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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Util\Filter;
use ReflectionClass;

trait TeamCityUtils
{
    public function testFailed(Test $test, \Exception $e, $time)
    {
        $params = [
            'name'    => $this->getTestName($test),
            'message' => TestFailure::exceptionToString($e),
            'details' => ' ' . str_replace("\n", "\n ", Filter::getFilteredStacktrace($e)),
            'duration'=> floor($time * 1000)
        ];

        if ($e instanceof ExpectationFailedException) {
            if ($comparisonFailure = $e->getComparisonFailure()) {
                $params += [
                    'type'     => 'testFailed',
                    'expected' => $comparisonFailure->getExpectedAsString(),
                    'actual'   => $comparisonFailure->getActualAsString()
                ];
            }
        }

        $this->message('testStdOut', [
            'name' => $this->getTestName($test),
            'out'  => sprintf("\n%s::%s", get_class($test), $this->getTestName($test))
        ]);

        $this->message('testFailed', $params);
    }

    /**
     * Represents a low-level mechanism for writing TeamCity Service Messages
     * to output.
     *
     * @see https://confluence.jetbrains.com/display/TCD9/Build+Script+Interaction+with+TeamCity
     *
     * @param string $name
     * @param array  $attributes
     */
    private function message($name, $attributes = [])
    {
        if ($flowId = $this->getFlowId()) {
            $attributes['flowId'] = $flowId;
        }

        $attributes = array_map(
            function ($key, $value) {
                return sprintf("%s='%s'", $key, $this->escape($value));
            },
            array_keys($attributes),
            array_values($attributes)
        );

        $attributeString = implode(' ', $attributes);

        $this->write(sprintf("\n##teamcity[%s %s]\n", $name, $attributeString));
    }

    /**
     * @return bool|int
     */
    private function getFlowId()
    {
        if (stripos(ini_get('disable_functions'), 'getmypid') === false) {
            return getmypid();
        } else {
            return false;
        }
    }

    protected function getTestName(Test $test)
    {
        if ($test instanceof TestCase) {
            $name = $test->getName();
        } elseif ($test instanceof TestSuite) {
            $name = $test->getName();
        } elseif ($test instanceof SelfDescribing) {
            $name = $test->toString();
        } else {
            $name = get_class($test);
        }

        return $name;
    }

    /**
     * @param  $string
     *
     * @return string
     */
    private function escape($string)
    {
        return str_replace(
            ['|', "'", "\n", "\r", ']', '['],
            ['||', "|'", '|n', '|r', '|]', '|['],
            $string
        );
    }

    private function getTestClassLocationHint(TestSuite $suite)
    {
        if (! $suite->isTestCase()) {
            return;
        }

        $path = $this->getFileName($suite->getName());

        return sprintf('php_qn://%s::\%s', $path, $suite->getName());
    }

    private function getTestMethodLocationHint(Test $test)
    {
        $className = get_class($test);
        $path      = $this->getFileName($className);

        return sprintf('php_qn://%s::\%s::%s', $path, $className, $test->getName());
    }

    /**
     * @param string $className
     *
     * @return string
     */
    private function getFileName($className)
    {
        return (new ReflectionClass($className))->getFileName();
    }
}
