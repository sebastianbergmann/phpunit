<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use function array_flip;
use function array_key_exists;
use function array_unique;
use function assert;
use function strtolower;
use function trim;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\Group;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\RequiresPhpExtension;
use PHPUnit\Metadata\UsesClass;
use PHPUnit\Metadata\UsesFunction;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Groups
{
    /**
     * @var array<string, list<non-empty-string>>
     */
    private static array $groupCache = [];

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     *
     * @return list<non-empty-string>
     */
    public function groups(string $className, string $methodName, bool $includeVirtual = true): array
    {
        $key = $className . '::' . $methodName . '::' . $includeVirtual;

        if (array_key_exists($key, self::$groupCache)) {
            return self::$groupCache[$key];
        }

        $groups = [];

        foreach (Registry::parser()->forClassAndMethod($className, $methodName)->isGroup() as $group) {
            assert($group instanceof Group);

            $groups[] = $group->groupName();
        }

        if (!$includeVirtual) {
            return self::$groupCache[$key] = array_unique($groups);
        }

        foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
            if ($metadata->isCoversClass()) {
                assert($metadata instanceof CoversClass);

                $groups[] = '__phpunit_covers_' . $this->canonicalizeName($metadata->className());

                continue;
            }

            if ($metadata->isCoversFunction()) {
                assert($metadata instanceof CoversFunction);

                $groups[] = '__phpunit_covers_' . $this->canonicalizeName($metadata->functionName());

                continue;
            }

            if ($metadata->isUsesClass()) {
                assert($metadata instanceof UsesClass);

                $groups[] = '__phpunit_uses_' . $this->canonicalizeName($metadata->className());

                continue;
            }

            if ($metadata->isUsesFunction()) {
                assert($metadata instanceof UsesFunction);

                $groups[] = '__phpunit_uses_' . $this->canonicalizeName($metadata->functionName());

                continue;
            }

            if ($metadata->isRequiresPhpExtension()) {
                assert($metadata instanceof RequiresPhpExtension);

                $groups[] = '__phpunit_requires_php_extension' . $this->canonicalizeName($metadata->extension());
            }
        }

        return self::$groupCache[$key] = array_unique($groups);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function size(string $className, string $methodName): TestSize
    {
        $groups = array_flip($this->groups($className, $methodName));

        if (isset($groups['large'])) {
            return TestSize::large();
        }

        if (isset($groups['medium'])) {
            return TestSize::medium();
        }

        if (isset($groups['small'])) {
            return TestSize::small();
        }

        return TestSize::unknown();
    }

    private function canonicalizeName(string $name): string
    {
        return strtolower(trim($name, '\\'));
    }
}
