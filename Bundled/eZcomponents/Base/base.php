<?php
/**
 * File containing the ezcBase class.
 *
 * @package Base
 * @version 1.0beta1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Base class implements the methods needed to use the eZ components.
 *
 * @package Base
 */
class ezcBase
{
    /**
     * Used for dependency checking, to check for a PHP extension.
     */
    const DEP_PHP_EXTENSION = "extension";

    /**
     * Used for dependency checking, to check for a PHP version.
     */
    const DEP_PHP_VERSION = "version";
    
    /**
     * Indirectly it determines the path where the autoloads are stored.
     */
    const libraryMode = "tarball";

    /**
     * @var string  The full path to the autoload directory.
     */
    protected static $packageDir;

    /**
     * @var array(string->array) Stores info with additional paths where
     *                           autoload files and classes for autoloading
     *                           could be found.  Each item of $repositoryDirs
     *                           looks like array( autoloadFileDir, baseDir ).
     */
    protected static $repositoryDirs = array();

    /**
     * @var array  This variable stores all the elements from the autoload
     *             arrays. When a new autoload file is loaded, their files
     *             are added to this array.
     */
    protected static $autoloadArray = array();

    /**
     * @var array  This variable stores all the elements from the autoload
     *             arrays for external repositories. When a new autoload file
     *             is loaded, their files are added to this array.
     */
    protected static $externalAutoloadArray = array();

    /**
     * Tries to autoload the given className. If the className could be found
     * this method returns true, otherwise false.
     *
     * This class caches the requested class names (including the ones who
     * failed to load).
     *
     *
     * @param string $className  The name of the class that should be loaded.
     *
     * @return bool
     */
    public static function autoload( $className )
    {
        ezcBase::setPackageDir();

        // Check whether the classname is already in the cached autoloadArray.
        if ( array_key_exists( $className, ezcBase::$autoloadArray ) )
        {
            // Is it registered as 'unloadable'?
            if ( ezcBase::$autoloadArray[$className] == false )
            {
                return false;
            }
            ezcBase::loadFile( ezcBase::$autoloadArray[$className] );

            return true;
        }

        // Check whether the classname is already in the cached autoloadArray
        // for external repositories..
        if ( array_key_exists( $className, ezcBase::$externalAutoloadArray ) )
        {
            // Is it registered as 'unloadable'?
            if ( ezcBase::$externalAutoloadArray[$className] == false )
            {
                return false;
            }
            ezcBase::loadExternalFile( ezcBase::$externalAutoloadArray[$className] );

            return true;
        }

        // Not cached, so load the autoload from the package.
        // Matches the first and optionally the second 'word' from the classname.
        if ( preg_match( "/^[a-z]*([A-Z][a-z]*)([A-Z][a-z]*)?/", $className, $matches ) !== false )
        {
            $autoloadFile = "";
            // Try to match with both names, if available.
            switch ( sizeof( $matches ) )
            {
                case 3:
                    $autoloadFile = strtolower( "{$matches[1]}_{$matches[2]}_autoload.php" );
                    if ( ezcBase::requireFile( $autoloadFile, $className ) )
                    {
                        return true;
                    }
                    // break intentionally missing.

                case 2:
                    $autoloadFile = strtolower( "{$matches[1]}_autoload.php" );
                    if ( ezcBase::requireFile( $autoloadFile, $className ) )
                    {
                        return true;
                    }
                    break;
            }

            // Maybe there is another autoload available.
            // Register this classname as false.
            ezcBase::$autoloadArray[$className] = false;
        }

        $path = ezcBase::$packageDir . 'autoload/';
        $realPath = realpath( $path );
        if ( $realPath == '' )
        {
            trigger_error( "Couldn't find autoload directory '$path'", E_USER_ERROR );
        }
        /* FIXME: this should go away - only for development */
        if ( self::libraryMode == 'devel' )
        {
            trigger_error( "Couldn't find autoload file for '$className' in '$realPath'", E_USER_WARNING );
        }
        return false;
    }

    /**
     * Returns the path to the autoload directory. The path depends on
     * the installation of the ezComponents. The SVN version has different
     * paths than the PEAR installed version. (For now).
     *
     * @return string
     */
    protected static function setPackageDir()
    {
        // Get the path to the components.
        $baseDir = dirname( __FILE__ );

        switch ( ezcBase::libraryMode )
        {
            case "devel":
            case "tarball":
                ezcBase::$packageDir = $baseDir. "/../../";
                break;
            case "pear";
                ezcBase::$packageDir = $baseDir. "/../";
                break;
        }
    }

    /**
     * Tries to load the autoload array and, if loaded correctly, includes the class.
     *
     * @param string $fileName    Name of the autoload file.
     * @param string $className   Name of the class that should be autoloaded.
     *
     * @return bool  True is returned when the file is correctly loaded.
     *                   Otherwise false is returned.
     */
    protected static function requireFile( $fileName, $className )
    {
        $autoloadDir = ezcBase::$packageDir . "autoload/";
        
        // We need the full path to the fileName. The method file_exists() doesn't
        // automatically check the (php.ini) library paths. Therefore:
        // file_exists( "ezc/autoload/$fileName" ) doesn't work.
        if ( file_exists( "$autoloadDir$fileName" ) )
        {
            $array = require( "$autoloadDir$fileName" );

            if ( is_array( $array) && array_key_exists( $className, $array ) )
            {
                // Add the array to the cache, and include the requested file.
                ezcBase::$autoloadArray = array_merge( ezcBase::$autoloadArray, $array );
                ezcBase::loadFile( ezcBase::$autoloadArray[$className] );
                return true;
            }
        }

        // It is not in components autoload/ dir.
        // try to search in additional dirs.
        foreach ( ezcBase::$repositoryDirs as $extraDir )
        {
            if ( file_exists( $extraDir['autoloadDirPath'] . '/' . $fileName ) )
            {
                $originalArray = require( $extraDir['autoloadDirPath'] . '/' . $fileName );

                // Building paths.
                // Resulting path to class definition file consists of:
                // path to extra directory with autoload file + 
                // basePath provided for current extra directory + 
                // path to class definition file stored in autoload file.
                foreach ( $originalArray as $class => $classPath )
                {
                    $array[$class] = $extraDir['basePath'] . '/' . $classPath;
                }

                if ( is_array( $array ) && array_key_exists( $className, $array ) )
                {
                    // Add the array to the cache, and include the requested file.
                    ezcBase::$externalAutoloadArray = array_merge( ezcBase::$externalAutoloadArray, $array );
                    ezcBase::loadExternalFile( ezcBase::$externalAutoloadArray[$className] );
                    return true;
                }
            }
        }

        //Nothing found :-(.
        return false;
    }

    /**
     * Loads, require(), the given file name. If we are in development mode,
     * "/src/" is inserted into the path.
     *
     * @param string $file  The name of the file that should be loaded.
     */
    protected static function loadFile( $file )
    {
        $originalFile = $file;
        list( $first, $second ) = explode( '/', $file, 2 );
        switch ( ezcBase::libraryMode )
        {
            case "devel":
            case "tarball":
                // Add the "src/" after the package name.
                $file = $first . "/src/" . $second;
                break;

            case "pear":
                $file = $first . '/'. $second;
                break;
        }

        if ( file_exists( ezcBase::$packageDir . $file ) ) 
        {
            require( ezcBase::$packageDir . $file );
        }
        else
        {
            throw new ezcBaseFileNotFoundException( ezcBase::$packageDir.$file );
        }
    }

    /**
     * Loads, require(), the given file name from an external package.
     *
     * @param string $file  The name of the file that should be loaded.
     */
    protected static function loadExternalFile( $file )
    {
        if ( file_exists( $file ) )
        {
            require( $file );
        }
        else
        {
            throw new ezcBaseFileNotFoundException( $file );
        }
    }

    /**
     * Checks for dependencies on PHP versions or extensions
     *
     * The function as called by the $component component checks for the $type
     * dependency. The dependency $type is compared against the $value. The
     * function aborts the script if the dependency is not matched.
     *
     * @param string $component
     * @param int $type
     * @param mixed $value
     */
    public static function checkDependency( $component, $type, $value )
    {
        switch ( $type )
        {
            case self::DEP_PHP_EXTENSION:
                if ( extension_loaded( $value ) )
                {
                    return;
                }
                else
                {
                    die( "\nThe {$component} component depends on the PHP extension '{$value}', which is not loaded.\n" );
                }
                break;

            case self::DEP_PHP_VERSION:
                $phpVersion = phpversion();
                if ( version_compare( $phpVersion, $value, '>=' ) )
                {
                    return;
                }
                else
                {
                    die( "\nThe {$component} component depends on the PHP version '{$value}', but the current version is '{$phpVersion}'.\n" );
                }
                break;
        }
    }

    /**
     * Return the list of directories that contain class repositories.
     * 
     * The path to the eZ components directory is always included in the result
     * array. Each element in the returned array has the format of:
     * packageDirectory => array( $type, autoloadDirectory )
     * The $type is either "ezc" for the eZ components directory or "external"
     * for external repositories.
     *
     * @return array(string=>array)
     * @access private
     */
    public static function getRepositoryDirectories()
    {
        $autoloadDirs = array();
        ezcBase::setPackageDir();
        $repositoryDir = realpath( dirname( __FILE__ ) . '/../../' );
        $autoloadDirs[$repositoryDir] = array( 'ezc', $repositoryDir . "/autoload" );

        foreach ( ezcBase::$repositoryDirs as $extraDirKey => $extraDirArray )
        {
            $autoloadDirs[$extraDirKey] = array( 'external', $extraDirArray['autoloadDirPath'] );
        }
        
        return $autoloadDirs;
    }

    /**
     * Adds an additional class repository.
     * 
     * Used for adding class repositoryies outside the eZ components to be
     * loaded by the autoload system.
     *
     * This function takes two arguments: $basePath is the base path for the
     * whole class repository and $autoloadDirPath the path where autoload
     * files for this repository are found. The paths in the autoload files are
     * relative to the package directory as specified by the $basePath
     * argument. I.e. class definition file will be searched at location
     * $basePath + path to the class definition file as stored in the autoload
     * file.
     * 
     * addClassRepository() should be called somewhere in code before external classes 
     * are used.
     *
     * Example:
     * Take the following facts:
     * <ul>
     * <li>there is a class repository stored in the directory "./repos"</li>
     * <li>autoload files for that repository are stored in "./repos/autoloads"</li>
     * <li>there are two components in this repository: "Me" and "You"</li>
     * <li>the "Me" component has the classes "erMyClass1" and "erMyClass2"</li>
     * <li>the "You" component has the classes "erYourClass1" and "erYourClass2"</li>
     * </ul>
     *
     * In this case you would need to create the following files in
     * "./repos/autoloads". Please note that the part before _autoload.php in
     * the filename is the first part of the <b>classname</b>, not considering
     * the all lower-case letter prefix.
     *
     * "my_autoload.php":
     * <code>
     * <?php
     *     return array (
     *       'erMyClass1' => 'Me/myclass1.php',
     *       'erMyClass2' => 'Me/myclass2.php',
     *     );
     * ?>
     * </code>
     *
     * "your_autoload.php":
     * <code>
     * <?php
     *     return array (
     *       'erYourClass1' => 'You/yourclass1.php',
     *       'erYourClass2' => 'You/yourclass2.php',
     *     );
     * ?>
     * </code>
     *
     * The directory structure for the external repository is then:
     * <code>
     * ./repos/autoloads/my_autoload.php
     * ./repos/autoloads/you_autoload.php
     * ./repos/Me/myclass1.php
     * ./repos/Me/myclass2.php
     * ./repos/You/yourclass1.php
     * ./repos/You/yourclass2.php
     * </code>

     * To use this repository with the autoload mechanism you have to use the
     * following code:
     * <code>
     * <?php
     * ezcBase::addClassRepository( './repos', './repos/autoloads' );
     * $myVar = new erMyClass2();
     * ?>
     * </code>
     * 
     * @throws ezcBaseFileNotFoundException if $autoloadDirPath or $basePath do not exist.
     * @param string $autoloadDirPath
     * @param string $basePath
     */
    public static function addClassRepository( $basePath, $autoloadDirPath = null )
    {
        // check if base path exists
        if ( !is_dir( $basePath ) ) 
        {
            throw new ezcBaseFileNotFoundException( $basePath, 'base directory' );
        }

        // calculate autoload path if it wasn't given
        if ( is_null( $autoloadDirPath ) )
        {
            $autoloadDirPath = $basePath . '/autoload';
        }

        // check if autoload dir exists
        if ( !is_dir( $autoloadDirPath ) ) 
        {
            throw new ezcBaseFileNotFoundException( $autoloadDirPath, 'autoload directory' );
        }

        // add info to $repositoryDirs
        // $autoloadDirPath will be used as a key in $repositoryDirs
        $array = array( $basePath => array( 'basePath' => $basePath, 'autoloadDirPath' => $autoloadDirPath ) );

        // add info to the list of extra dirs if it exists there it will not be doubled.
        ezcBase::$repositoryDirs = array_merge( ezcBase::$repositoryDirs, $array );
    }
}
?>
