<?php
/**
 * File containing the ezcBaseFileNotFoundException class
 *
 * @package Base
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcBaseFileNotFoundException is thrown when a file or directory was tried to
 * be opened, but did not exist.
 *
 * @package Base
 * @version 1.1
 */
class ezcBaseFileNotFoundException extends ezcBaseFileException
{
    /**
     * Constructs a new ezcBaseFileNotFoundException.
     *
     * @param string $name The name of the file.
     * @param string $type The type of the file.
     * @param string $message A string with extra information.
     */
    function __construct( $path, $type = null, $message = null )
    {
        $typePart = '';
        if ( $type )
        {
            $typePart = "$type ";
        }

        $messagePart = '';
        if ( $message )
        {
            $messagePart = " ($message)";
        }

        parent::__construct( "The {$typePart}file <{$path}> could not be found.$messagePart" );
    }
}
?>
