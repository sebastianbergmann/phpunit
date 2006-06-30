<?php
/**
 * File containing the ezcBaseSettingNotFoundException class.
 *
 * @package Base
 * @version 1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * ezcBaseSettingNotFoundException is thrown whenever there is a name passed as
 * part as the options array to setOptions() for an option that doesn't exist.
 *
 * @package Base
 */
class ezcBaseSettingNotFoundException extends ezcBaseException
{
    /**
     * Constructs a new ezcBaseSettingNotFoundException for $settingName.
     *
     * @param string $settingName The name of the setting that does not exist.
     */
    function __construct( $settingName )
    {
        parent::__construct( "The setting <{$settingName}> is not a valid configuration setting." );
    }
}
?>
