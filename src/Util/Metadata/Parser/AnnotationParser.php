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

use const JSON_THROW_ON_ERROR;
use function array_shift;
use function count;
use function explode;
use function json_decode;
use function strlen;
use function strpos;
use function substr;
use function trim;
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
                    $this->parseCovers($values, $result);

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
                    $this->parseUses($values, $result);

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
                    $this->parseCovers($values, $result);

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
                        if (strpos($value, '::') !== false) {
                            $result[] = new Depends(...explode('::', $value));

                            continue;
                        }

                        $result[] = new Depends($className, $value);
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

                case 'test':
                    $result[] = new Test;

                    break;

                case 'testdox':
                    $result[] = new TestDox($values[0]);

                    break;

                case 'testWith':
                    foreach ($values as $value) {
                        $result[] = new TestWith(json_decode($value, true, 512, JSON_THROW_ON_ERROR));
                    }

                    break;

                case 'uses':
                    $this->parseUses($values, $result);

                    break;
            }
        }

        return MetadataCollection::fromArray($result);
    }

    private function parseCovers(array $values, array &$result): void
    {
        foreach ($values as $value) {
            if (strpos($value, '::') === 0) {
                $result[] = new CoversFunction(trim(substr($value, strlen('::')), '\\'));

                continue;
            }

            if (strpos($value, '::') !== false) {
                $result[] = new CoversMethod(...explode('::', $value));

                continue;
            }

            $result[] = new CoversClass(trim($value, '\\'));
        }
    }

    private function parseUses(array $values, array &$result): void
    {
        foreach ($values as $value) {
            if (strpos($value, '::') === 0) {
                $result[] = new UsesFunction(trim(substr($value, strlen('::')), '\\'));

                continue;
            }

            if (strpos($value, '::') !== false) {
                $result[] = new UsesMethod(...explode('::', $value));

                continue;
            }

            $result[] = new UsesClass(trim($value, '\\'));
        }
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
                $parts    = explode(' ', substr($value, strlen('PHP ')));
                $operator = '>=';

                if (count($parts) === 1) {
                    $version = $parts[0];
                } elseif (count($parts) === 2) {
                    [$operator, $version] = $parts;
                }

                $result[] = new RequiresPhp($version, $operator);

                continue;
            }

            if (strpos($value, 'PHPUnit ') === 0) {
                $parts    = explode(' ', substr($value, strlen('PHPUnit ')));
                $operator = '>=';

                if (count($parts) === 1) {
                    $version = $parts[0];
                } elseif (count($parts) === 2) {
                    [$operator, $version] = $parts;
                }

                $result[] = new RequiresPhpunit($version, $operator);

                continue;
            }

            if (strpos($value, 'extension ') === 0) {
                $parts     = explode(' ', substr($value, strlen('extension ')));
                $extension = array_shift($parts);
                $version   = null;
                $operator  = '>=';

                if (count($parts) === 1) {
                    $version = $parts[0];
                } elseif (count($parts) === 2) {
                    [$operator, $version] = $parts;
                }

                $result[] = new RequiresPhpExtension($extension, $version, $operator);
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
}
