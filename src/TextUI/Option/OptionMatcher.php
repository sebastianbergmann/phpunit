<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputDefinition;

/**
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_Option_OptionMatcher
{
    /**
     * @var InputDefinition
     */
    private $definition;

    /**
     * @param InputDefinition $definition
     */
    public function __construct(InputDefinition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Return the possible matches for an input string.
     *
     * @param $inputOption
     *
     * @return array An array containing possible options matching the $inputOption
     */
    public function match($inputOption)
    {
        $inputName = $this->extractInputName($inputOption);
        if (empty($inputName)) {
            return [];
        }

        if ($this->definition->hasOption($inputName) || $this->definition->hasShortcut($inputName)) {
            return [$inputName]; // option is already correctly formatted
        }

        $possibleMatch = [];
        foreach ($this->definition->getOptions() as $option) {
            $optionName = $option->getName();
            if (0 === strpos($optionName, $inputName)) {
                $possibleMatch[$inputName] = $optionName;
            }
        }

        return $possibleMatch;
    }

    /**
     * Extract option name by removing the parts "--" and anything after "=" or " "
     *
     * @param string $inputOption
     *
     * @return string
     */
    private function extractInputName($inputOption)
    {
        $inputName = trim($inputOption, '-');
        $equalPosition = strpos($inputName, '=');
        $spacePosition = strpos($inputName, ' ');
        if (false !== $equalPosition) {
            $inputName = substr($inputName, 0, $equalPosition);
        }

        if (false !== $spacePosition) {
            $inputName = substr($inputName, 0, $spacePosition);
            return $inputName;
        }

        return $inputName;
    }
}
