<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.4
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * PDO helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.4
 */
class PHPUnit_Util_PDO
{
    public static function factory($dsn)
    {
        $parsed = self::parseDSN($dsn);

        switch ($parsed['phptype']) {
            case 'mysql': {
                $database      = NULL;
                $charset       = NULL;
                $host          = NULL;
                $port          = NULL;
                $socket        = NULL;
                $user          = NULL;
                $pass          = NULL;
                $driverOptions = NULL;

                foreach ( $parsed as $key => $val )
                {
                    switch ( $key )
                    {
                        case 'database':
                        case 'dbname':
                            $database = $val;
                            break;

                        case 'charset':
                            $charset = $val;
                            break;

                        case 'host':
                        case 'hostspec':
                            $host = $val;
                            break;

                        case 'port':
                            $port = $val;
                            break;

                        case 'socket':
                            $socket = $val;
                            break;

                        case 'user':
                        case 'username':
                            $user = $val;
                            break;

                        case 'pass':
                        case 'password':
                            $pass = $val;
                            break;

                        case 'driver-opts':
                            $driverOptions = $val;
                            break;
                    }
                }

                if ( !isset( $database ) )
                {
                    throw new InvalidArgumentException('Invalid DSN.');
                }

                $dsn = "mysql:dbname=$database";

                if ( isset( $host ) && $host )
                {
                    $dsn .= ";host=$host";
                }

                if ( isset( $port ) && $port )
                {
                    $dsn .= ";port=$port";
                }

                if ( isset( $charset ) && $charset )
                {
                    $dsn .= ";charset=$charset";
                }

                if ( isset( $socket ) && $socket )
                {
                    $dsn .= ";unix_socket=$socket";
                }

                $dbh = new PDO($dsn, $user, $pass, $driverOptions);
            }
            break;

            case 'sqlite': {
                $dbh = new PDO($dsn);
            }
            break;

            default: {
                throw new InvalidArgumentException('Invalid DSN.');
            }
        }

        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $dbh;
    }

    /**
     * Returns the Data Source Name as a structure containing the various parts of the DSN.
     *
     * Additional keys can be added by appending a URI query string to the
     * end of the DSN.
     *
     * The format of the supplied DSN is in its fullest form:
     * <code>
     *  phptype(dbsyntax)://username:password@protocol+hostspec/database?option=8&another=true
     * </code>
     *
     * Most variations are allowed:
     * <code>
     *  phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
     *  phptype://username:password@hostspec/database_name
     *  phptype://username:password@hostspec
     *  phptype://username@hostspec
     *  phptype://hostspec/database
     *  phptype://hostspec
     *  phptype(dbsyntax)
     *  phptype
     * </code>
     *
     * This function is 'borrowed' from PEAR /DB.php .
     *
     * @param string $dsn Data Source Name to be parsed
     *
     * @return array an associative array with the following keys:
     *  + phptype:  Database backend used in PHP (mysql, odbc etc.)
     *  + dbsyntax: Database used with regards to SQL syntax etc.
     *  + protocol: Communication protocol to use (tcp, unix etc.)
     *  + hostspec: Host specification (hostname[:port])
     *  + database: Database to use on the DBMS server
     *  + username: User name for login
     *  + password: Password for login
     */
    public static function parseDSN( $dsn )
    {
        if ( $dsn == 'sqlite::memory:' )
        {
            $dsn = 'sqlite://:memory:';
        }

        $parsed = array(
            'phptype'  => FALSE,
            'dbsyntax' => FALSE,
            'username' => FALSE,
            'password' => FALSE,
            'protocol' => FALSE,
            'hostspec' => FALSE,
            'port'     => FALSE,
            'socket'   => FALSE,
            'database' => FALSE,
        );

        if ( is_array( $dsn ) )
        {
            $dsn = array_merge( $parsed, $dsn );
            if ( !$dsn['dbsyntax'] )
            {
                $dsn['dbsyntax'] = $dsn['phptype'];
            }
            return $dsn;
        }

        // Find phptype and dbsyntax
        if ( ( $pos = strpos( $dsn, '://' ) ) !== FALSE )
        {
            $str = substr( $dsn, 0, $pos );
            $dsn = substr( $dsn, $pos + 3 );
        }
        else
        {
            $str = $dsn;
            $dsn = NULL;
        }

        // Get phptype and dbsyntax
        // $str => phptype(dbsyntax)
        if ( preg_match( '|^(.+?)\((.*?)\)$|', $str, $arr ) )
        {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        }
        else
        {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }

        if ( !count( $dsn ) )
        {
            return $parsed;
        }

        // Get (if found): username and password
        // $dsn => username:password@protocol+hostspec/database
        if ( ( $at = strrpos( (string) $dsn, '@' ) ) !== FALSE )
        {
            $str = substr( $dsn, 0, $at );
            $dsn = substr( $dsn, $at + 1 );
            if ( ( $pos = strpos( $str, ':' ) ) !== FALSE )
            {
                $parsed['username'] = rawurldecode( substr( $str, 0, $pos ) );
                $parsed['password'] = rawurldecode( substr( $str, $pos + 1 ) );
            }
            else
            {
                $parsed['username'] = rawurldecode( $str );
            }
        }

        // Find protocol and hostspec

        if ( preg_match( '|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match ) )
        {
            // $dsn => proto(proto_opts)/database
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : FALSE;
            $dsn         = $match[3];
        }
        else
        {
            // $dsn => protocol+hostspec/database (old format)
            if ( strpos( $dsn, '+' ) !== FALSE )
            {
                list( $proto, $dsn ) = explode( '+', $dsn, 2 );
            }
            if ( strpos( $dsn, '/' ) !== FALSE )
            {
                list( $proto_opts, $dsn ) = explode( '/', $dsn, 2 );
            }
            else
            {
                $proto_opts = $dsn;
                $dsn = NULL;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = ( !empty( $proto ) ) ? $proto : 'tcp';
        $proto_opts = rawurldecode( $proto_opts );
        if ( $parsed['protocol'] == 'tcp' )
        {
            if ( strpos( $proto_opts, ':' ) !== FALSE )
            {
                list( $parsed['hostspec'], $parsed['port'] ) = explode( ':', $proto_opts );
            }
            else
            {
                $parsed['hostspec'] = $proto_opts;
            }
        }
        elseif ( $parsed['protocol'] == 'unix' )
        {
            $parsed['socket'] = $proto_opts;
        }

        // Get dabase if any
        // $dsn => database
        if ( $dsn )
        {
            if ( ( $pos = strpos( $dsn, '?' ) ) === FALSE )
            {
                // /database
                $parsed['database'] = rawurldecode( $dsn );
            }
            else
            {
                // /database?param1=value1&param2=value2
                $parsed['database'] = rawurldecode( substr( $dsn, 0, $pos ) );
                $dsn = substr( $dsn, $pos + 1 );
                if ( strpos( $dsn, '&') !== FALSE )
                {
                    $opts = explode( '&', $dsn );
                }
                else
                { // database?param1=value1
                    $opts = array( $dsn );
                }
                foreach ( $opts as $opt )
                {
                    list( $key, $value ) = explode( '=', $opt );
                    if ( !isset( $parsed[$key] ) )
                    {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode( $value );
                    }
                }
            }
        }
        return $parsed;
    }
}
?>