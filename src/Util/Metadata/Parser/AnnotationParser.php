<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

use function array_shift;
use function explode;
use function implode;
use function strlen;
use function strpos;
use function substr;
use PHPUnit\Util\Exception;
use PHPUnit\Util\Metadata\Annotation\Registry as AnnotationRegistry;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class AnnotationParser implements Parser
{
    /**
     * @psalm-param class-string $className
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

                case 'requires':
                    $this->parseRequires($values, $result);

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

        return MetadataCollection::fromArray($result);
    }

    /**
     * @psalm-param class-string $className
     */
    public function forMethod(string $className, string $methodName): MetadataCollection
    {
        try {
            $annotations = AnnotationRegistry::getInstance()->forMethod($className, $methodName)->symbolAnnotations();
        } catch (Exception $e) {
            return MetadataCollection::fromArray([]);
        }

        $result = [];

        foreach ($annotations as $annotation => $values) {
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

                case 'requires':
                    $this->parseRequires($values, $result);

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

                case 'uses':
                    foreach ($values as $value) {
                        $value = $this->cleanUpCoversOrUsesTarget($value);

                        $result[] = new Uses($value);
                    }

                    break;
            }
        }

        return MetadataCollection::fromArray($result);
    }

    private function parseRequires(array $values, array &$result): void
    {
        foreach ($values as $value) {
            if (strpos($value, 'function ') === 0) {
                $result[] = new RequiresFunction(substr($value, strlen('function ')));

                continue;
            }

            if (strpos($value, 'OS ') === 0) {
                $result[] = new RequiresOperatingSystem(substr($value, strlen('OS ')));

                continue;
            }

            if (strpos($value, 'OSFAMILY ') === 0) {
                $result[] = new RequiresOperatingSystemFamily(substr($value, strlen('OSFAMILY ')));

                continue;
            }

            if (strpos($value, 'PHP ') === 0) {
                $result[] = new RequiresPhp(substr($value, strlen('PHP ')));

                continue;
            }

            if (strpos($value, 'PHPUnit ') === 0) {
                $result[] = new RequiresPhpunit(substr($value, strlen('PHPUnit ')));

                continue;
            }

            if (strpos($value, 'extension ') === 0) {
                $parts              = explode(' ', substr($value, strlen('extension ')));
                $extension          = array_shift($parts);
                $versionRequirement = null;

                if (!empty($parts)) {
                    $versionRequirement = implode(' ', $parts);
                }

                $result[] = new RequiresPhpExtension(
                    $extension,
                    $versionRequirement
                );
            }
        }
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
}
