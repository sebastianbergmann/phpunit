<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use PHPUnit\Util\Test as TestUtil;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestBuilder
{
    /**
     * @throws \ReflectionException
     */
    public function build(\ReflectionClass $theClass, string $name): Test
    {
        $className = $theClass->getName();

        if (!$theClass->isInstantiable()) {
            return new WarningTestCase(
                \sprintf('Cannot instantiate class "%s".', $className)
            );
        }

        $backupSettings = TestUtil::getBackupSettings(
            $className,
            $name
        );

        $preserveGlobalState = TestUtil::getPreserveGlobalStateSettings(
            $className,
            $name
        );

        $runTestInSeparateProcess = TestUtil::getProcessIsolationSettings(
            $className,
            $name
        );

        $runClassInSeparateProcess = TestUtil::getClassProcessIsolationSettings(
            $className,
            $name
        );

        $constructor = $theClass->getConstructor();

        if ($constructor === null) {
            throw new Exception('No valid test provided.');
        }

        $parameters = $constructor->getParameters();

        // TestCase() or TestCase($name)
        if (\count($parameters) < 2) {
            $test = new $className;
        } // TestCase($name, $data)
        else {
            try {
                $data = TestUtil::getProvidedData(
                    $className,
                    $name
                );
            } catch (IncompleteTestError $e) {
                $message = \sprintf(
                    'Test for %s::%s marked incomplete by data provider',
                    $className,
                    $name
                );

                $_message = $e->getMessage();

                if (!empty($_message)) {
                    $message .= "\n" . $_message;
                }

                $data = new IncompleteTestCase($className, $name, $message);
            } catch (SkippedTestError $e) {
                $message = \sprintf(
                    'Test for %s::%s skipped by data provider',
                    $className,
                    $name
                );

                $_message = $e->getMessage();

                if (!empty($_message)) {
                    $message .= "\n" . $_message;
                }

                $data = new SkippedTestCase($className, $name, $message);
            } catch (\Throwable $t) {
                $message = \sprintf(
                    'The data provider specified for %s::%s is invalid.',
                    $className,
                    $name
                );

                $_message = $t->getMessage();

                if (!empty($_message)) {
                    $message .= "\n" . $_message;
                }

                $data = new WarningTestCase($message);
            }

            // Test method with @dataProvider.
            if (isset($data)) {
                $test = new DataProviderTestSuite(
                    $className . '::' . $name
                );

                if (empty($data)) {
                    $data = new WarningTestCase(
                        \sprintf(
                            'No tests found in suite "%s".',
                            $test->getName()
                        )
                    );
                }

                $groups = TestUtil::getGroups($className, $name);

                if ($data instanceof WarningTestCase ||
                    $data instanceof SkippedTestCase ||
                    $data instanceof IncompleteTestCase) {
                    $test->addTest($data, $groups);
                } else {
                    foreach ($data as $_dataName => $_data) {
                        $_test = new $className($name, $_data, $_dataName);

                        \assert($_test instanceof TestCase);

                        if ($runTestInSeparateProcess) {
                            $_test->setRunTestInSeparateProcess(true);

                            if ($preserveGlobalState !== null) {
                                $_test->setPreserveGlobalState($preserveGlobalState);
                            }
                        }

                        if ($runClassInSeparateProcess) {
                            $_test->setRunClassInSeparateProcess(true);

                            if ($preserveGlobalState !== null) {
                                $_test->setPreserveGlobalState($preserveGlobalState);
                            }
                        }

                        if ($backupSettings['backupGlobals'] !== null) {
                            $_test->setBackupGlobals(
                                $backupSettings['backupGlobals']
                            );
                        }

                        if ($backupSettings['backupStaticAttributes'] !== null) {
                            $_test->setBackupStaticAttributes(
                                $backupSettings['backupStaticAttributes']
                            );
                        }

                        $test->addTest($_test, $groups);
                    }
                }
            } else {
                $test = new $className;
            }
        }

        if ($test instanceof TestCase) {
            $test->setName($name);

            if ($runTestInSeparateProcess) {
                $test->setRunTestInSeparateProcess(true);

                if ($preserveGlobalState !== null) {
                    $test->setPreserveGlobalState($preserveGlobalState);
                }
            }

            if ($runClassInSeparateProcess) {
                $test->setRunClassInSeparateProcess(true);

                if ($preserveGlobalState !== null) {
                    $test->setPreserveGlobalState($preserveGlobalState);
                }
            }

            if ($backupSettings['backupGlobals'] !== null) {
                $test->setBackupGlobals($backupSettings['backupGlobals']);
            }

            if ($backupSettings['backupStaticAttributes'] !== null) {
                $test->setBackupStaticAttributes(
                    $backupSettings['backupStaticAttributes']
                );
            }
        }

        return $test;
    }
}
