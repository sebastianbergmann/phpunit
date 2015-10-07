<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_Input implements InputInterface
{
    /**
     * @var InputInterface
     */
    private $wrappedInput;

    /**
     * @var PHPUnit_TextUI_InputDefinition
     */
    private $definition;

    public function __construct(array $argv)
    {
        $this->definition = PHPUnit_TextUI_ConsoleInputDefinition::defaultDefinition();
        $this->wrappedInput = new ArgvInput(
            $this->autoCorrectArguments($argv),
            $this->definition
        );
    }

    /**
     * Ensure support of Github#1330
     *
     * @param array $argv
     *
     * @return array
     */
    private function autoCorrectArguments(array $argv)
    {
        $matcher = new PHPUnit_TextUI_Option_OptionMatcher($this->definition);
        foreach ($argv as $index => $inputOption) {
            $possibleMatch = $matcher->match($inputOption);

            // replace option with the only match
            $this->guardAgainstUnresolvableOption($inputOption, $possibleMatch);

            if (1 === count($possibleMatch)) {
                $aliases = array_keys($possibleMatch);
                $realName = array_pop($possibleMatch);
                $alias = array_pop($aliases);

                $argv[$index] = str_ireplace($alias, $realName, $argv[$index]);
            }
        }

        return $argv;
    }

    /**
     * Returns the option value for a given option name.
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws \InvalidArgumentException When option given doesn't exist
     */
    public function getOption($name)
    {
        $value = $this->wrappedInput->getOption($name);

        $option = $this->definition->getOption($name);
        // In case the option comes from the console component
        if ($option instanceof PHPUnit_TextUI_Option_Option) {
            /**
             * @var $option PHPUnit_TextUI_Option_Option
             */
            $value = $option->convertValue($value);
        }

        return $value;
    }

    /**
     * Returns the first argument from the raw parameters (not parsed).
     *
     * @return string The value of the first argument or null otherwise
     */
    public function getFirstArgument()
    {
        return $this->wrappedInput->getFirstArgument();
    }

    /**
     * Returns true if the raw parameters (not parsed) contain a value.
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values The values to look for in the raw parameters (can be an array)
     *
     * @return bool true if the value is contained in the raw parameters
     */
    public function hasParameterOption($values)
    {
        return $this->wrappedInput->hasParameterOption($values);
    }

    /**
     * Returns the value of a raw option (not parsed).
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values The value(s) to look for in the raw parameters (can be an array)
     * @param mixed $default The default value to return if no result is found
     *
     * @return mixed The option value
     */
    public function getParameterOption($values, $default = false)
    {
        return $this->wrappedInput->getParameterOption($values, $default);
    }

    /**
     * Binds the current Input instance with the given arguments and options.
     *
     * @param \Symfony\Component\Console\Input\InputDefinition $definition A InputDefinition instance
     */
    public function bind(\Symfony\Component\Console\Input\InputDefinition $definition)
    {
        $this->wrappedInput->bind($definition);
    }

    /**
     * Validates if arguments given are correct.
     *
     * Throws an exception when not enough arguments are given.
     *
     * @throws \RuntimeException
     */
    public function validate()
    {
        $this->wrappedInput->validate();
    }

    /**
     * Returns all the given arguments merged with the default values.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->wrappedInput->getArguments();
    }

    /**
     * Gets argument by name.
     *
     * @param string $name The name of the argument
     *
     * @return mixed
     */
    public function getArgument($name)
    {
        return $this->wrappedInput->getArgument($name);
    }

    /**
     * Sets an argument value by name.
     *
     * @param string $name The argument name
     * @param string $value The argument value
     *
     * @throws \InvalidArgumentException When argument given doesn't exist
     */
    public function setArgument($name, $value)
    {
        $this->wrappedInput->setArgument($name, $value);
    }

    /**
     * Returns true if an InputArgument object exists by name or position.
     *
     * @param string|int $name The InputArgument name or position
     *
     * @return bool true if the InputArgument object exists, false otherwise
     */
    public function hasArgument($name)
    {
        return $this->wrappedInput->hasArgument($name);
    }

    /**
     * Returns all the given options merged with the default values.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->wrappedInput->getOptions();
    }

    /**
     * Sets an option value by name.
     *
     * @param string $name The option name
     * @param string|bool $value The option value
     *
     * @throws \InvalidArgumentException When option given doesn't exist
     */
    public function setOption($name, $value)
    {
        return $this->wrappedInput->setOption($name, $value);
    }

    /**
     * Returns true if an InputOption object exists by name.
     *
     * @param string $name The InputOption name
     *
     * @return bool true if the InputOption object exists, false otherwise
     */
    public function hasOption($name)
    {
        return $this->wrappedInput->hasOption($name);
    }

    /**
     * Is this input means interactive?
     *
     * @return bool
     */
    public function isInteractive()
    {
        return $this->wrappedInput->isInteractive();
    }

    /**
     * Sets the input interactivity.
     *
     * @param bool $interactive If the input should be interactive
     */
    public function setInteractive($interactive)
    {
        return $this->wrappedInput->setInteractive($interactive);
    }

    /**
     * @param $inputOption
     * @param array $possibleMatch
     *
     * @throws RuntimeException
     */
    private function guardAgainstUnresolvableOption($inputOption, array $possibleMatch)
    {
        if (count($possibleMatch) > 1) {
            throw new PHPUnit_Framework_Exception(
                sprintf(
                    "Ambiguous option '%s' cannot be resolved. Possible matches are '%s'.",
                    $inputOption,
                    implode(',', $possibleMatch)
                )
            );
        }
    }
}
