<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util\TestDox;

/**
 * Prettifies class and method names for use in TestDox documentation.
 */
final class NamePrettifier
{
    /**
     * @var array
     */
    private $strings = [];

    /**
     * Prettifies the name of a test class.
     *
     * @param string $name
     *
     * @return string
     */
    public function prettifyTestClass(string $name): string
    {
        $title = $name;

        if (\substr($name, -1 * \strlen('Test')) === 'Test') {
            $title = \substr($title, 0, \strripos($title, 'Test'));
        }

        if (\strpos($name, 'Test') === 0) {
            $title = \substr($title, \strlen('Test'));
        }

        # When a class name starts with Tests\\ we should strip off the
        # remaining s\\, since Test is removed above
        if (\substr($name, 0, \strlen('Tests')) === 'Tests') {
            $title = \substr($title, \strlen('s\\'));
        }

        if ($title[0] === '\\') {
            $title = \substr($title, 1);
        }

        return $title;
    }

    /**
     * Prettifies the name of a test method.
     *
     * @param string $name
     *
     * @return string
     */
    public function prettifyTestMethod(string $name): string
    {
        $buffer = '';

        if (!\is_string($name) || $name === '') {
            return $buffer;
        }

        $string = \preg_replace('#\d+$#', '', $name, -1, $count);

        if (\in_array($string, $this->strings)) {
            $name = $string;
        } elseif ($count === 0) {
            $this->strings[] = $string;
        }

        if (\strpos($name, 'test') === 0) {
            $name = \substr($name, 4);
        }

        if ($name === '') {
            return $buffer;
        }

        $name[0] = \strtoupper($name[0]);

        if (\strpos($name, '_') !== false) {
            return \trim(\str_replace('_', ' ', $name));
        }

        $max        = \strlen($name);
        $wasNumeric = false;

        for ($i = 0; $i < $max; $i++) {
            if ($i > 0 && \ord($name[$i]) >= 65 && \ord($name[$i]) <= 90) {
                $buffer .= ' ' . \strtolower($name[$i]);
            } else {
                $isNumeric = \is_numeric($name[$i]);

                if (!$wasNumeric && $isNumeric) {
                    $buffer .= ' ';
                    $wasNumeric = true;
                }

                if ($wasNumeric && !$isNumeric) {
                    $wasNumeric = false;
                }

                $buffer .= $name[$i];
            }
        }

        return $buffer;
    }
}
