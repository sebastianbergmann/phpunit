<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Annotation;

use PharIo\Version\VersionConstraintParser;
use PHPUnit\Framework\Warning;

/**
 * This is an abstraction around a PHPUnit-specific docBlock,
 * allowing us to ask meaningful questions about a specific
 * reflection symbol.
 *
 * @internal This class is part of PHPUnit internals, an not intended
 *           for downstream usage
 *
 * @psalm-immutable
 */
final class DocBlock
{
    private const REGEX_REQUIRES_VERSION = '/@requires\s+(?P<name>PHP(?:Unit)?)\s+(?P<operator>[<>=!]{0,2})\s*(?P<version>[\d\.-]+(dev|(RC|alpha|beta)[\d\.])?)[ \t]*\r?$/m';
    private const REGEX_REQUIRES_VERSION_CONSTRAINT = '/@requires\s+(?P<name>PHP(?:Unit)?)\s+(?P<constraint>[\d\t \-.|~^]+)[ \t]*\r?$/m';
    private const REGEX_REQUIRES_OS = '/@requires\s+(?P<name>OS(?:FAMILY)?)\s+(?P<value>.+?)[ \t]*\r?$/m';
    private const REGEX_REQUIRES_SETTING = '/@requires\s+(?P<name>setting)\s+(?P<setting>([^ ]+?))\s*(?P<value>[\w\.-]+[\w\.]?)?[ \t]*\r?$/m';
    private const REGEX_REQUIRES = '/@requires\s+(?P<name>function|extension)\s+(?P<value>([^\s<>=!]+))\s*(?P<operator>[<>=!]{0,2})\s*(?P<version>[\d\.-]+[\d\.]?)?[ \t]*\r?$/m';

    /** @var \ReflectionClass|\ReflectionFunctionAbstract */
    private $reflector;

    private function __construct()
    {
    }

    public static function ofClass(\ReflectionClass $class) : self
    {
        $instance = new self();

        $instance->reflector = $class;

        return $instance;
    }

    public static function ofFunction(\ReflectionFunctionAbstract $function) : self
    {
        $instance = new self();

        $instance->reflector = $function;

        return $instance;
    }

    // @TODO accurately document returned type here
    public function requirements() : array
    {
        $docComment = (string) $this->reflector->getDocComment();
        $offset     = $this->reflector->getStartLine();
        $requires   = [
            '__OFFSET' => [
                '__FILE' => \realpath($this->reflector->getFileName()),
            ],
        ];

        // Split docblock into lines and rewind offset to start of docblock
        $lines = \preg_split('/\r\n|\r|\n/', $docComment);
        $offset -= \count($lines);

        foreach ($lines as $line) {
            if (\preg_match(self::REGEX_REQUIRES_OS, $line, $matches)) {
                $requires[$matches['name']]             = $matches['value'];
                $requires['__OFFSET'][$matches['name']] = $offset;
            }

            if (\preg_match(self::REGEX_REQUIRES_VERSION, $line, $matches)) {
                $requires[$matches['name']] = [
                    'version'  => $matches['version'],
                    'operator' => $matches['operator'],
                ];
                $requires['__OFFSET'][$matches['name']] = $offset;
            }

            if (\preg_match(self::REGEX_REQUIRES_VERSION_CONSTRAINT, $line, $matches)) {
                if (!empty($requires[$matches['name']])) {
                    $offset++;

                    continue;
                }

                try {
                    $versionConstraintParser = new VersionConstraintParser;

                    $requires[$matches['name'] . '_constraint'] = [
                        'constraint' => $versionConstraintParser->parse(\trim($matches['constraint'])),
                    ];
                    $requires['__OFFSET'][$matches['name'] . '_constraint'] = $offset;
                } catch (\PharIo\Version\Exception $e) {
                    throw new Warning($e->getMessage(), $e->getCode(), $e);
                }
            }

            if (\preg_match(self::REGEX_REQUIRES_SETTING, $line, $matches)) {
                if (!isset($requires['setting'])) {
                    $requires['setting'] = [];
                }
                $requires['setting'][$matches['setting']]                 = $matches['value'];
                $requires['__OFFSET']['__SETTING_' . $matches['setting']] = $offset;
            }

            if (\preg_match(self::REGEX_REQUIRES, $line, $matches)) {
                $name = $matches['name'] . 's';

                if (!isset($requires[$name])) {
                    $requires[$name] = [];
                }

                $requires[$name][]                                                = $matches['value'];
                $requires['__OFFSET'][$matches['name'] . '_' . $matches['value']] = $offset;

                if ($name === 'extensions' && !empty($matches['version'])) {
                    $requires['extension_versions'][$matches['value']] = [
                        'version'  => $matches['version'],
                        'operator' => $matches['operator'],
                    ];
                }
            }

            $offset++;
        }

        return $requires;
    }
}
