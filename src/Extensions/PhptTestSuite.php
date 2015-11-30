<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

/**
 * Suite for .phpt test cases.
 *
 * @since Class available since Release 3.1.4
 */
class PHPUnit_Extensions_PhptTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Constructs a new TestSuite for .phpt test cases.
     *
     * @param string $directory
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct(string $directory)
    {
        if (!is_dir($directory)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'directory name');
        }

        $this->setName($directory);

        $facade = new File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, '.phpt');

        foreach ($files as $file) {
            $this->addTestFile($file);
        }
    }
}
