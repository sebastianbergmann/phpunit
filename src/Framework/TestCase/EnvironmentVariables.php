<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestCase;

use function assert;
use function putenv;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\WithEnvironmentVariable;
use PHPUnit\Runner\BackedUpEnvironmentVariable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class EnvironmentVariables
{
    /**
     * @var list<BackedUpEnvironmentVariable>
     */
    private array $backup = [];

    /**
     * Backs up and then sets the environment variables that are configured
     * using #[WithEnvironmentVariable] for the given test method.
     *
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    public function set(string $className, string $methodName): void
    {
        $withEnvironmentVariables = MetadataRegistry::parser()->forClassAndMethod($className, $methodName)->isWithEnvironmentVariable();

        $environmentVariables = [];

        foreach ($withEnvironmentVariables as $metadata) {
            assert($metadata instanceof WithEnvironmentVariable);

            $environmentVariables[$metadata->environmentVariableName()] = $metadata->value();
        }

        foreach ($environmentVariables as $environmentVariableName => $environmentVariableValue) {
            $this->backup = [...$this->backup, ...BackedUpEnvironmentVariable::create($environmentVariableName)];

            if ($environmentVariableValue === null) {
                unset($_ENV[$environmentVariableName]);
                putenv($environmentVariableName);
            } else {
                $_ENV[$environmentVariableName] = $environmentVariableValue;
                putenv("{$environmentVariableName}={$environmentVariableValue}");
            }
        }
    }

    public function restore(): void
    {
        foreach ($this->backup as $backedUpEnvironmentVariable) {
            $backedUpEnvironmentVariable->restore();
        }

        $this->backup = [];
    }
}
