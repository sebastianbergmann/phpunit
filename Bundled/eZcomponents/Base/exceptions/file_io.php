<?php
/**
 * File containing the ezcBaseFileIoException class
 *
 * @package Base
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcBaseFileIoException is thrown when a problem occurs while writing
 * and reading to/from an open file.
 *
 * @package Base
 * @version 1.1
 */
class ezcBaseFileIoException extends ezcBaseFileException
{
    /**
     * Constructs a new ezcBaseFileIoException for the file $path.
     *
     * @param string $name The name of the file.
     * @param int    $mode The mode of the property that is allowed
     *               (ezcBaseFileException::READ, ezcBaseFileException::WRITE,
     *               ezcBaseFileException::EXECUTE or
     *               ezcBaseFileException::CHANGE).
     * @param string $message A string with extra information.
     */
    function __construct( $path, $mode, $message = null )
    {
        switch ( $mode )
        {
            case ezcBaseFileException::READ:
                $operation = "An error occurred while reading from <{$path}>";
                break;
            case ezcBaseFileException::WRITE:
                $operation = "An error occurred while writing to <{$path}>";
                break;
        }

        $messagePart = '';
        if ( $message )
        {
            $messagePart = " ($message)";
        }

        parent::__construct( "$operation.$messagePart" );
    }
}
?>
