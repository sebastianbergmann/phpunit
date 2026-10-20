<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\RuleSet;

use Composer\Semver\Semver;
use PhpCsFixer\ComposerJsonReader;
use PhpCsFixer\ConfigurationException\UnresolvableAutoRuleSetConfigurationException;

/**
 * @internal
 *
 * @phpstan-require-implements AutomaticRuleSetDefinitionInterface
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
trait AutomaticMigrationSetTrait
{
    private function calculateTargetSet(string $setName, string $entity, bool $isRisky): string
    {
        static $set = null;

        if (null === $set) {
            $actualVersion = self::calculateActualVersion($entity);

            $candidates = self::calculateCandidateSets($entity, $isRisky);
            $composerCandidates = Semver::rsort(array_keys($candidates));

            foreach ($composerCandidates as $candidate) {
                if (Semver::satisfies($actualVersion, '>='.$candidate)) {
                    $set = $candidates[$candidate]; // @phpstan-ignore offsetAccess.notFound

                    break;
                }
            }

            if (null === $set) {
                throw new UnresolvableAutoRuleSetConfigurationException(\sprintf('No migration set found feasible for %s (%s %s).', $setName, $entity, $actualVersion));
            }
        }

        return $set;
    }

    /**
     * @return list<AbstractMigrationSetDefinition>
     */
    private static function getMigrationSets(): array
    {
        static $sets = null;

        if (null === $sets) {
            $sets = array_values(array_filter(
                RuleSets::getSetDefinitions(),
                static fn (RuleSetDefinitionInterface $set): bool => !($set instanceof DeprecatedRuleSetDefinitionInterface) && is_subclass_of($set, AbstractMigrationSetDefinition::class),
            ));
        }

        return $sets;
    }

    private static function calculateActualVersion(string $entity): string
    {
        $composerJsonReader = ComposerJsonReader::createSingleton();

        if ('PHP' === $entity) {
            $version = $composerJsonReader->getPhp();
        } elseif ('PHPUnit' === $entity) {
            $version = $composerJsonReader->getPhpUnit();
        } else {
            throw new \InvalidArgumentException(\sprintf('Entity "%s" is not supported.', $entity));
        }

        if (null === $version) {
            throw new UnresolvableAutoRuleSetConfigurationException(\sprintf('Cannot detect %s version from "composer.json".', $entity));
        }

        return $version;
    }

    /**
     * @return array<string, string> [ 'major.minor' => '@SetName', ... ]
     */
    private static function calculateCandidateSets(string $entity, bool $isRisky): array
    {
        $candidates = [];
        foreach (self::getMigrationSets() as $set) {
            if ($set->getEntity() !== $entity) {
                continue;
            }

            if ($set->isRisky() !== $isRisky) {
                continue;
            }

            $candidates[$set->getVersionMajorMinor()] = $set->getName();
        }

        return $candidates;
    }
}
