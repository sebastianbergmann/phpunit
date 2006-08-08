<?php
/**
 * File containing the ezcConsoleOutput class.
 *
 * @package ConsoleTools
 * @version 1.1.1
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @filesource
 */

/**
 * Class for handling console output.
 *
 * The ezcConsoleOutput class provides an interface to output text to the console. It deals with formating 
 * text in different ways and offers some comfortable options to deal
 * with console text output.
 *
 * <code>
 * // Create the output handler
 * $out = new ezcConsoleOutput();
 * 
 * // Set the verbosity to level 10
 * $out->options->verbosityLevel = 10;
 * // Enable auto wrapping of lines after 40 characters
 * $out->options->autobreak    = 40;
 * 
 * // Set the color of the default output format to green
 * $out->formats->default->color   = 'green';
 * 
 * // Set the color of the output format named 'success' to white
 * $out->formats->success->color   = 'white';
 * // Set the style of the output format named 'success' to bold
 * $out->formats->success->style   = array( 'bold' );
 * 
 * // Set the color of the output format named 'failure' to red
 * $out->formats->failure->color   = 'red';
 * // Set the style of the output format named 'failure' to bold
 * $out->formats->failure->style   = array( 'bold' );
 * // Set the background color of the output format named 'failure' to blue
 * $out->formats->failure->bgcolor = 'blue';
 * 
 * // Output text with default format
 * $out->outputText( 'This is default text ' );
 * // Output text with format 'success'
 * $out->outputText( 'including success message', 'success' );
 * // Some more output with default output.
 * $out->outputText( "and a manual linebreak.\n" );
 * 
 * // Manipulate the later output
 * $out->formats->success->color = 'green';
 * $out->formats->default->color = 'blue';
 * 
 * // This is visible, since we set verboseLevel to 10, and printed in default format (now blue)
 * $out->outputText( "Some verbose output.\n", null, 10 );
 * // This is not visible, since we set verboseLevel to 10
 * $out->outputText( "Some more verbose output.\n", null, 20 );
 * // This is visible, since we set verboseLevel to 10, and printed in format 'failure'
 * $out->outputText( "And some not so verbose, failure output.\n", 'failure', 5 );
 * </code>
 *
 * For a list of valid colors, style attributes and background colors, please 
 * refer to {@link ezcConsoleOutputFormat}.
 * 
 * @package ConsoleTools
 * @version 1.1.1
 */
class ezcConsoleOutput
{
    /**
     * Options
     * 
     * @var ezcConsoleOutputOptions
     */
    protected $options;

    /**
     * Formats 
     * 
     * @var ezcConsoleOutputFormats
     */
    protected $formats;

    /**
     * Whether a position has been stored before, using the storePos() method.
     *
     * @see ezcConsoleOutput::storePos()
     * @var bool
     */
    protected $positionStored = false;

    /**
     * Stores the mapping of color names to their escape
     * sequence values.
     *
     * @var array(string=>int)
     */
    protected static $color = array(
    	'gray'          => 30,
    	'red'           => 31,
    	'green'         => 32,
    	'yellow'        => 33,
    	'blue'          => 34,
    	'magenta'       => 35,
    	'cyan'          => 36,
    	'white'         => 37,
        'default'       => 39
    );

    /**
     * Stores the mapping of bgcolor names to their escape
     * sequence values.
     * 
     * @var array(string=>int)
     */
    protected static $bgcolor = array(
        'black'      => 40,
        'red'        => 41,
    	'green'      => 42,
    	'yellow'     => 43,
    	'blue'       => 44,
    	'magenta'    => 45,
    	'cyan'       => 46,
    	'white'      => 47,
        'default'    => 49,
    );

    /**
     * Stores the mapping of styles names to their escape
     * sequence values.
     * 
     * @var array(string=>int)
     */
    protected static $style = array( 
        'default'           => '0',
    
        'bold'              => 1,
        'faint'             => 2,
        'normal'            => 22,
        
        'italic'            => 3,
        'notitalic'         => 23,
        
        'underlined'        => 4,
        'doubleunderlined'  => 21,
        'notunderlined'     => 24,
        
        'blink'             => 5,
        'blinkfast'         => 6,
        'noblink'           => 25,
        
        'negative'          => 7,
        'positive'          => 27,
    );

    /**
     * Basic escape sequence string. Use sprintf() to insert escape codes.
     * 
     * @var string
     */
    private $escapeSequence = "\033[%sm";

    /**
     * Create a new console output handler.
     *
     * @see ezcConsoleOutput::$options
     * @see ezcConsoleOutputOptions
     * @see ezcConsoleOutput::$formats
     * @see ezcConsoleOutputFormats
     *
     * @param ezcConsoleOutputFormats $formats Formats to be used for output.
     * @param array(string=>string) $options   Options to set.
     */
    public function __construct( ezcConsoleOutputFormats $formats = null, array $options = array() )
    {
        $options = isset( $options ) ? $options : new ezcConsoleOutputOptions();
        $formats = isset( $formats ) ? $formats : new ezcConsoleOutputFormats();
        $this->options = new ezcConsoleOutputOptions( $options );
        $this->formats = $formats;
    }
    
    /**
     * Set new options.
     * This method allows you to change the options of an output handler.
     *  
     * @param ezcConsoleOutputOptions $options The options to set.
     *
     * @throws ezcBaseSettingNotFoundException
     *         If you tried to set a non-existent option value. 
     * @throws ezcBaseSettingValueException
     *         If the value is not valid for the desired option.
     * @throws ezcBaseValueException
     *         If you submit neither an array nor an instance of 
     *         ezcConsoleOutputOptions.
     */
    public function setOptions( $options ) 
    {
        if ( is_array( $options ) ) 
        {
            $this->options->merge( $options );
        } 
        else if ( $options instanceof ezcConsoleOutputOptions ) 
        {
            $this->options = $options;
        }
        else
        {
            throw new ezcBaseValueException( "options", $options, "instance of ezcConsoleOutputOptions" );
        }
    }

    /**
     * Returns the current options.
     * Returns the options currently set for this output handler.
     * 
     * @return ezcConsoleOutputOptions The current options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Property read access.
     *
     * @throws ezcBasePropertyNotFoundException 
     *         If the the desired property is not found.
     * 
     * @param string $propertyName Name of the property.
     * @return mixed Value of the property or null.
     */
    public function __get( $propertyName )
    {
        switch ( $propertyName ) 
        {
            case 'options':
                return $this->options;
            case 'formats':
                return $this->formats;
            default:
                break;
        }
        throw new ezcBasePropertyNotFoundException( $propertyName );
    }

    /**
     * Property write access.
     * 
     * @param string $propertyName Name of the property.
     * @param mixed $val  The value for the property.
     *
     * @throws ezcBaseValueException 
     *         If a the value for the property options is not an instance of 
     *         ezcConsoleOutputOptions. 
     * @throws ezcBaseValueException 
     *         If a the value for the property formats is not an instance of 
     *         ezcConsoleOutputFormats. 
     * @return void
     */
    public function __set( $propertyName, $val )
    {
        switch ( $propertyName ) 
        {
            case 'options':
                if ( !( $val instanceof ezcConsoleOutputOptions ) )
                {
                    throw new ezcBaseValueException( $key, $val, 'ezcConsoleOutputOptions' );
                }
                $this->options = $val;
                return;
            case 'formats':
                if ( !( $val instanceof ezcConsoleOutputFormats ) )
                {
                    throw new ezcBaseValueException( $key, $val, 'ezcConsoleOutputFormats' );
                }
                $this->formats = $val;
                return;
            default:
                break;
        }
        throw new ezcBasePropertyNotFoundException( $propertyName );
    }
 
    /**
     * Property isset access.
     * 
     * @param string $propertyName Name of the property.
     * @return bool True is the property is set, otherwise false.
     */
    public function __isset( $propertyName )
    {
        switch ( $propertyName )
        {
            case 'options':
            case 'formats':
                return true;
        }
        return false;
    }

    /**
     * Print text to the console.
     *
     * Output a string to the console. If $format parameter is omitted, 
     * the default style is chosen. Style can either be a special style
     * {@link ezcConsoleOutput::$options}, a style name 
     * {@link ezcConsoleOutput$formats} or 'none' to print without any styling.
     *
     * @param string $text        The text to print.
     * @param string $format      Format chosen for printing.
     * @param int $verbosityLevel On which verbose level to output this message.
     * @return void
     */
    public function outputText( $text, $format = 'default', $verbosityLevel = 1 ) 
    {
        if ( $this->options->verbosityLevel >= $verbosityLevel ) 
        {
            if ( is_int( $this->options->autobreak ) && $this->options->autobreak > 0 )
            {
                $textLines = explode( "\n", $text );
                foreach ( $textLines as $id => $textLine )
                {
                    $textLines[$id] = wordwrap( $textLine, $this->options->autobreak, "\n", true );
                }
                $text = implode( "\n", $textLines );
            }
            echo ( $this->options->useFormats == true ) ? $this->formatText( $text, $format ) : $text;
        }
    }

    /**
     * Print text to the console and automatically append a line break.
     *
     * This method acts similar to {@link ezcConsoleOutput::outputText()}, in
     * fact it even uses it. The difference is, that outputLine()
     * automatically appends a manual line break to the printed text. Besides
     * that, you can leave out the $text parameter of outputLine() to only
     * print a line break.
     * 
     * @param string $text        The text to print.
     * @param string $format      Format chosen for printing.
     * @param int $verbosityLevel On which verbose level to output this message.
     * @return void
     */
    public function outputLine( $text = '', $format = 'default', $verbosityLevel = 1 )
    {
        $this->outputText( $text, $format, $verbosityLevel );
        $this->outputText( PHP_EOL, null, $verbosityLevel );
    }

    /**
     * Returns a formated version of the text.
     *
     * If $format parameter is omitted, the default style is chosen. The format
     * must be a valid registered format definition.  For information on the
     * formats, see {@link ezcConsoleOutput::$formats}.
     *
     * @param string $text   Text to apply style to.
     * @param string $format Format chosen to be applied.
     * @return string
     */
    public function formatText( $text, $format = 'default' ) 
    {
        return $this->buildSequence( $format ) . $text . $this->buildSequence( 'default' );
    }

    /**
     * Stores the current cursor position.
     *
     * Saves the current cursor position to return to it using 
     * {@link ezcConsoleOutput::restorePos()}. Multiple calls
     * to this method will override each other. Only the last
     * position is saved.
     *
     * @return void
     */
    public function storePos() 
    {
        echo "\033[s";
        $this->positionStored = true;
    }

    /**
     * Restores a cursor position.
     *
     * Restores the cursor position last saved using {@link
     * ezcConsoleOutput::storePos()}.
     *
     * @todo Gnome terminal does not recognize this codes. Solution??
     *
     * @throws ezcConsoleNoPositionStoredException 
     *         If no position is saved.
     * @return void
     */
    public function restorePos() 
    {
        if ( $this->positionStored === false )
        {
            throw new ezcConsoleNoPositionStoredException();
        }
        echo "\033[u";
    }

    /**
     * Move the cursor to a specific column of the current line.
     *
     * Moves the cursor to a specific column index of the current line (default
     * is 1).
     * 
     * @param int $column Column to jump to.
     * @return void
     */
    public function toPos( $column = 1 ) 
    {
        echo "\033[{$column}G";
    }

    /**
     * Returns if a format code is valid for ta specific formating option.
     *
     * This method determines if a given code is valid for a specific
     * formatting option ('color', 'bgcolor' or 'style').
     * 
     * @see ezcConsoleOutput::getFormatCode();
     *
     * @param string $type Formating type.
     * @param string $key  Format option name.
     * @return bool True if the code is valid.
     */
    public static function isValidFormatCode( $type, $key )
    {
        return isset( self::${$type}[$key] );
    }

    /**
     * Returns the escape sequence for a specific format.
     *
     * Returns the default format escape sequence, if the requested format does
     * not exist.
     * 
     * @param string $format Name of the format.
     * @return string The escape sequence.
     */
    protected function buildSequence( $format = 'default' )
    {
        if ( $format === 'default' )
        {
            return sprintf( $this->escapeSequence, 0 );
        }
        $modifiers = array();
        $formats = array( 'color', 'style', 'bgcolor' );
        foreach ( $formats as $formatType ) 
        {
            // Get modifiers
            if ( is_array( $this->formats->$format->$formatType ) )
            {
                if ( !in_array( 'default', $this->formats->$format->$formatType ) )
                {
                    foreach ( $this->formats->$format->$formatType as $singleVal ) 
                    {
                        $modifiers[] = $this->getFormatCode( $formatType, $singleVal );
                    }
                }
            }
            else
            {
                if ( $this->formats->$format->$formatType !== 'default' )
                {
                    $modifiers[] = $this->getFormatCode( $formatType, $this->formats->$format->$formatType );
                }
            }
        }
        // Merge modifiers
        return sprintf( $this->escapeSequence, implode( ';', $modifiers ) );
    }

    /**
     * Returns the code for a given formating option of a given type.
     *
     * $type is the type of formating ('color', 'bgcolor' or 'style'), $key the
     * name of the format to lookup. Returns the numeric code for the requested
     * format or 0 if format or type do not exist.
     * 
     * @see ezcConsoleOutput::isValidFormatCode()
     * 
     * @param string $type Formatting type.
     * @param string $key  Format option name.
     * @return int The code representation.
     */
    protected function getFormatCode( $type, $key )
    {
        if ( !ezcConsoleOutput::isValidFormatCode( $type, $key ) ) 
        {
            return 0;
        }
        return ezcConsoleOutput::${$type}[$key];
    }

}
?>
