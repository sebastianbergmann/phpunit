<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputOption;

/**
 * @since Class available since Release 6.0.0
 */
final class PHPUnit_TextUI_Option_CoverageTextStdOut extends PHPUnit_TextUI_Option_Option
{
    public function __construct()
    {
        parent::__construct(
            'coverage-text-stdout',
            null,
            InputOption::VALUE_NONE,
            'Generate code coverage report in text format using "php://stdout".'
        );
    }

    /**
     * Convert a value to another format supported by the option.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertValue($value)
    {
        if (true === $value) {
            return 'php://stdout';
        }

        // Force null to make sure that isset returns false when switch not active
        return null;
    }
}
