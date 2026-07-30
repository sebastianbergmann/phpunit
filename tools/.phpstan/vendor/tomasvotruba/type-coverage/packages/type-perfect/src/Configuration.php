<?php

declare(strict_types=1);

namespace Rector\TypePerfect;

final class Configuration
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private array $parameters
    ) {
        // enabed by default in tests
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            $this->parameters['narrow_param'] = true;
            $this->parameters['narrow_return'] = true;
            $this->parameters['no_mixed'] = true;
            $this->parameters['null_over_false'] = true;
            $this->parameters['no_param_type_removal'] = true;
            $this->parameters['no_array_access_on_object'] = true;
            $this->parameters['no_isset_on_object'] = true;
            $this->parameters['no_empty_on_object'] = true;
        }
    }

    public function isNarrowParamEnabled(): bool
    {
        return $this->parameters['narrow_param'] ?? false;
    }

    public function isNarrowReturnEnabled(): bool
    {
        return $this->parameters['narrow_return'] ?? false;
    }

    public function isNoMixedPropertyEnabled(): bool
    {
        if ($this->parameters['no_mixed_property']) {
            return true;
        }

        return $this->parameters['no_mixed'] ?? false;
    }

    public function isNoMixedCallerEnabled(): bool
    {
        if ($this->parameters['no_mixed_caller']) {
            return true;
        }

        return $this->parameters['no_mixed'] ?? false;
    }

    public function isNoFalsyReturnEnabled(): bool
    {
        return $this->parameters['null_over_false'] ?? false;
    }

    public function isNoParamTypeRemovalEnabled(): bool
    {
        return $this->parameters['no_param_type_removal'] ?? false;
    }

    public function isNoArrayAccessOnObjectEnabled(): bool
    {
        return $this->parameters['no_array_access_on_object'] ?? false;
    }

    public function isNoIssetOnObjectEnabled(): bool
    {
        return $this->parameters['no_isset_on_object'] ?? false;
    }

    public function isNoEmptyOnObjectEnabled(): bool
    {
        return $this->parameters['no_empty_on_object'] ?? false;
    }
}
