<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Metadata;

/**
 * Attribute and annotation information is static within a single PHP process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Registry
{
    private static ?Reader $instance = null;

    public static function reader(): Reader
    {
        return self::$instance ?? self::$instance = self::build();
    }

    private function __construct()
    {
    }

    private static function build(): Reader
    {
        if (PHP_MAJOR_VERSION >= 8) {
            return new CachingReader(
                new MergingParser(
                    new AttributeReader,
                    new AnnotationReader
                )
            );
        }

        return new CachingReader(new AnnotationReader);
    }
}
