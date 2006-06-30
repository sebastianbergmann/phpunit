<?php
/**
 * File containing the ezcBaseFilePermissionException class
 *
 * @package Base
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcBaseFilePermissionException is thrown whenever a permission problem with
 * a file, directory or stream occurred.
 *
 * @package Base
 * @version 1.1
 */
class ezcBaseFilePermissionException extends ezcBaseFileException
{
    /**
     * Constructs a new ezcPropertyPermissionException for the property $name.
     *
     * @param string $name The name of the file.
     * @param int    $mode The mode of the property that is allowed
     *               (ezcBaseFileException::READ, ezcBaseFileException::WRITE,
     *               ezcBaseFileException::EXECUTE,
     *               ezcBaseFileException::CHANGE or
     *               ezcBaseFileException::REMOVE).
     * @param string $message A string with extra information.
     */
    function __construct( $path, $mode, $message = null )
    {
        switch ( $mode )
        {
            case ezcBaseFileException::READ:
                $operation = "The file <{$path}> can not be opened for reading";
                break;
            case ezcBaseFileException::WRITE:
                $operation = "The file <{$path}> can not be opened for writing";
                break;
            case ezcBaseFileException::EXECUTE:
                $operation = "The file <{$path}> can not be executed";
                break;
            case ezcBaseFileException::CHANGE:
                $operation = "The permissions for <{$path}> can not be changed";
                break;
            case ezcBaseFileException::REMOVE:
                $operation = "The file <{$path}> can not be removed";
                break;
            case ( ezcBaseFileException::READ || ezcBaseFileException::WRITE ):
                $operation = "The file <{$path}> can not be opened for reading and writing";
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
