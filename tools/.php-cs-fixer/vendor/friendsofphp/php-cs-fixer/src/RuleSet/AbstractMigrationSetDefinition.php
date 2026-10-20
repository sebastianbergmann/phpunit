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

use PhpCsFixer\Preg;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractMigrationSetDefinition extends AbstractRuleSetDefinition
{
    private string $entity;

    /** @var array{'major': int, 'minor': int} */
    private array $version;

    public function __construct()
    {
        parent::__construct();
        $this->parseRuleSetName();
    }

    /**
     * @internal
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getVersionMajorMinor(): string
    {
        return \sprintf('%s.%s', $this->version['major'], $this->version['minor']);
    }

    public function getDescription(): string
    {
        $improvement = [
            'PHPUnit' => 'tests code',
        ][$this->getEntity()] ?? 'code';

        return \sprintf('Rules to improve %s for %s %s compatibility.', $improvement, $this->getEntity(), $this->getVersionMajorMinor());
    }

    private function parseRuleSetName(): void
    {
        $name = $this->getName();

        // @TODO v4 - `x?` -> `x` @MARKER_deprecated_migration_name_pattern
        if (Preg::match('#^@PHPUnit(\d+)x?(\d)Migration.*$#', $name, $matches)) {
            $this->entity = 'PHPUnit';
            $this->version = [
                'major' => (int) $matches[1],
                'minor' => (int) $matches[2],
            ];

            return;
        }

        // @TODO v4 - `x?` -> `x` @MARKER_deprecated_migration_name_pattern
        if (Preg::match('#^@PHP(\d)x?(\d)Migration.*$#', $name, $matches)) {
            $this->entity = 'PHP';
            $this->version = [
                'major' => (int) $matches[1],
                'minor' => (int) $matches[2],
            ];

            return;
        }

        throw new \RuntimeException(\sprintf('Cannot generate name of "%s" / "%s".', static::class, $name));
    }
}
