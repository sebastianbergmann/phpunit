<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use function array_merge;
use function explode;
use function method_exists;
use function strlen;
use function strpos;
use function substr;
use PHPUnit\Framework\Warning;
use PHPUnit\Metadata\Annotation\Registry as AnnotationRegistry;
use PHPUnit\Util\VersionComparisonOperator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class AnnotationParser implements Parser
{
    /**
     * @psalm-param class-string $className
     *
     * @throws \PHPUnit\Util\Exception
     * @throws Warning
     */
    public function forClass(string $className): MetadataCollection
    {
        $result = [];

        foreach (AnnotationRegistry::getInstance()->forClassName($className)->symbolAnnotations() as $annotation => $values) {
            switch ($annotation) {
                case 'backupGlobals':
                    $result[] = new BackupGlobals($this->stringToBool($values[0]));

                    break;

                case 'backupStaticAttributes':
                    $result[] = new BackupStaticProperties($this->stringToBool($values[0]));

                    break;

                case 'codeCoverageIgnore':
                    $result[] = new CodeCoverageIgnore;

                    break;

                case 'covers':
                    foreach ($values as $value) {
                        $value = $this->cleanUpCoversOrUsesTarget($value);

                        $result[] = new Covers($value);
                    }

                    break;

                case 'coversDefaultClass':
                    $result[] = new CoversDefaultClass($values[0]);

                    break;

                case 'coversNothing':
                    $result[] = new CoversNothing;

                    break;

                case 'doesNotPerformAssertions':
                    $result[] = new DoesNotPerformAssertions;

                    break;

                case 'group':
                case 'ticket':
                    foreach ($values as $value) {
                        $result[] = new Group($value);
                    }

                    break;

                case 'large':
                    $result[] = new Group('large');

                    break;

                case 'medium':
                    $result[] = new Group('medium');

                    break;

                case 'preserveGlobalState':
                    $result[] = new PreserveGlobalState($this->stringToBool($values[0]));

                    break;

                case 'runClassInSeparateProcess':
                    $result[] = new RunClassInSeparateProcess;

                    break;

                case 'runTestsInSeparateProcesses':
                    $result[] = new RunTestsInSeparateProcesses;

                    break;

                case 'small':
                    $result[] = new Group('small');

                    break;

                case 'testdox':
                    $result[] = new TestDox($values[0]);

                    break;

                case 'todo':
                    $result[] = new Todo;

                    break;

                case 'uses':
                    foreach ($values as $value) {
                        $value = $this->cleanUpCoversOrUsesTarget($value);

                        $result[] = new Uses($value);
                    }

                    break;

                case 'usesDefaultClass':
                    $result[] = new UsesDefaultClass($values[0]);

                    break;
            }
        }

        $result = array_merge(
            $result,
            $this->parseRequirements(
                AnnotationRegistry::getInstance()->forClassName($className)->requirements()
            )
        );

        return MetadataCollection::fromArray($result);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws \PHPUnit\Util\Exception
     * @throws Warning
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        $result = [];

        foreach (AnnotationRegistry::getInstance()->forMethod($className, $methodName)->symbolAnnotations() as $annotation => $values) {
            switch ($annotation) {
                case 'after':
                    $result[] = new After;

                    break;

                case 'afterClass':
                    $result[] = new AfterClass;

                    break;

                case 'backupGlobals':
                    $result[] = new BackupGlobals($this->stringToBool($values[0]));

                    break;

                case 'backupStaticAttributes':
                    $result[] = new BackupStaticProperties($this->stringToBool($values[0]));

                    break;

                case 'before':
                    $result[] = new Before;

                    break;

                case 'beforeClass':
                    $result[] = new BeforeClass;

                    break;

                case 'codeCoverageIgnore':
                    $result[] = new CodeCoverageIgnore;

                    break;

                case 'covers':
                    foreach ($values as $value) {
                        $value = $this->cleanUpCoversOrUsesTarget($value);

                        $result[] = new Covers($value);
                    }

                    break;

                case 'coversNothing':
                    $result[] = new CoversNothing;

                    break;

                case 'dataProvider':
                    foreach ($values as $value) {
                        if (strpos($value, '::') !== false) {
                            $result[] = new DataProvider(...explode('::', $value));

                            continue;
                        }

                        $result[] = new DataProvider($className, $value);
                    }

                    break;

                case 'depends':
                    foreach ($values as $value) {
                        $deepClone    = false;
                        $shallowClone = false;

                        if (strpos($value, 'clone ') === 0) {
                            $deepClone = true;
                            $value     = substr($value, strlen('clone '));
                        } elseif (strpos($value, 'shallowClone ') === 0) {
                            $shallowClone = true;
                            $value        = substr($value, strlen('shallowClone '));
                        }

                        if (strpos($value, '::') !== false) {
                            [$className, $methodName] = explode('::', $value);

                            $result[] = new Depends($className, $methodName, $deepClone, $shallowClone);

                            continue;
                        }

                        $result[] = new Depends($className, $value, $deepClone, $shallowClone);
                    }

                    break;

                case 'doesNotPerformAssertions':
                    $result[] = new DoesNotPerformAssertions;

                    break;

                case 'group':
                case 'ticket':
                    foreach ($values as $value) {
                        $result[] = new Group($value);
                    }

                    break;

                case 'large':
                    $result[] = new Group('large');

                    break;

                case 'medium':
                    $result[] = new Group('medium');

                    break;

                case 'postCondition':
                    $result[] = new PostCondition;

                    break;

                case 'preCondition':
                    $result[] = new PreCondition;

                    break;

                case 'preserveGlobalState':
                    $result[] = new PreserveGlobalState($this->stringToBool($values[0]));

                    break;

                case 'runInSeparateProcess':
                    $result[] = new RunInSeparateProcess;

                    break;

                case 'small':
                    $result[] = new Group('small');

                    break;

                case 'test':
                    $result[] = new Test;

                    break;

                case 'testdox':
                    $result[] = new TestDox($values[0]);

                    break;

                case 'todo':
                    $result[] = new Todo;

                    break;

                case 'uses':
                    foreach ($values as $value) {
                        $value = $this->cleanUpCoversOrUsesTarget($value);

                        $result[] = new Uses($value);
                    }

                    break;
            }
        }

        if (method_exists($className, $methodName)) {
            $result = array_merge(
                $result,
                $this->parseRequirements(
                    AnnotationRegistry::getInstance()->forMethod($className, $methodName)->requirements()
                )
            );
        }

        return MetadataCollection::fromArray($result);
    }

    /**
     * @psalm-param class-string $className
     *
     * @throws \PHPUnit\Util\Exception
     * @throws Warning
     */
    public function forClassAndMethod(string $className, string $methodName): MetadataCollection
    {
        return $this->forClass($className)->mergeWith(
            $this->forMethod($className, $methodName)
        );
    }

    private function stringToBool(string $value): ?bool
    {
        if ($value === 'enabled') {
            return true;
        }

        if ($value === 'disabled') {
            return false;
        }

        return null;
    }

    private function cleanUpCoversOrUsesTarget(string $value): string
    {
        $value = preg_replace('/[\s()]+$/', '', $value);

        return explode(' ', $value, 2)[0];
    }

    /**
     * @psalm-return list<Metadata>
     */
    private function parseRequirements(array $requirements): array
    {
        $result = [];

        if (!empty($requirements['PHP'])) {
            $result[] = new RequiresPhp(
                new VersionComparisonRequirement(
                    $requirements['PHP']['version'],
                    new VersionComparisonOperator(empty($requirements['PHP']['operator']) ? '>=' : $requirements['PHP']['operator'])
                )
            );
        } elseif (!empty($requirements['PHP_constraint'])) {
            $result[] = new RequiresPhp(
                new VersionConstraintRequirement($requirements['PHP_constraint']['constraint'])
            );
        }

        if (!empty($requirements['extensions'])) {
            foreach ($requirements['extensions'] as $extension) {
                if (isset($requirements['extension_versions'][$extension])) {
                    continue;
                }

                $result[] = new RequiresPhpExtension($extension, null);
            }
        }

        if (!empty($requirements['extension_versions'])) {
            foreach ($requirements['extension_versions'] as $extension => $version) {
                $result[] = new RequiresPhpExtension(
                    $extension,
                    new VersionComparisonRequirement(
                        $version['version'],
                        new VersionComparisonOperator(empty($version['operator']) ? '>=' : $version['operator'])
                    )
                );
            }
        }

        if (!empty($requirements['PHPUnit'])) {
            $result[] = new RequiresPhpunit(
                new VersionComparisonRequirement(
                    $requirements['PHPUnit']['version'],
                    new VersionComparisonOperator(empty($requirements['PHPUnit']['operator']) ? '>=' : $requirements['PHPUnit']['operator'])
                )
            );
        } elseif (!empty($requirements['PHPUnit_constraint'])) {
            $result[] = new RequiresPhpunit(
                new VersionConstraintRequirement($requirements['PHPUnit_constraint']['constraint'])
            );
        }

        if (!empty($requirements['OSFAMILY'])) {
            $result[] = new RequiresOperatingSystemFamily($requirements['OSFAMILY']);
        }

        if (!empty($requirements['OS'])) {
            $result[] = new RequiresOperatingSystem($requirements['OS']);
        }

        if (!empty($requirements['functions'])) {
            foreach ($requirements['functions'] as $function) {
                $pieces = explode('::', $function);

                if (count($pieces) === 2) {
                    $result[] = new RequiresMethod($pieces[0], $pieces[1]);
                } else {
                    $result[] = new RequiresFunction($function);
                }
            }
        }

        if (!empty($requirements['setting'])) {
            foreach ($requirements['setting'] as $setting => $value) {
                $result[] = new RequiresSetting($setting, $value);
            }
        }

        return $result;
    }
}
