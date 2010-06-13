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
 * @author     Sean Coates <sean@caedmon.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.0
 */

require_once 'PHPUnit/Extensions/TicketListener.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A test listener that interact with Trac.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sean Coates <sean@caedmon.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.0
 */
class PHPUnit_Extensions_TicketListener_Trac extends PHPUnit_Extensions_TicketListener
{
    protected $username;
    protected $password;
    protected $hostpath;
    protected $scheme;

    /**
     * Constructor
     *
     * @param string $user Trac-XMLRPC username
     * @param string $pass Trac-XMLRPC password
     * @param string $hostpath Trac-XMLRPC Host+Path (e.g. example.com/trac/login/xmlrpc)
     * @param string $scheme Trac scheme (http or https)
     */
    public function __construct($username, $password, $hostpath, $scheme = 'http')
    {
        $this->username = $username;
        $this->password = $password;
        $this->hostpath = $hostpath;
        $this->scheme   = $scheme;
    }

    protected function updateTicket($ticketId, $newStatus, $message, $resolution)
    {
        if (PHPUnit_Util_Filesystem::fileExistsInIncludePath('XML/RPC2/Client.php')) {
            PHPUnit_Util_Filesystem::collectStart();
            require_once 'XML/RPC2/Client.php';

            $ticket = XML_RPC2_Client::create(
              $this->scheme . '://' .
              $this->username . ':' . $this->password . '@' .
              $this->hostpath,
              array('prefix' => 'ticket.')
            );

            try {
                $ticketInfo = $ticket->get($ticketId);
            }

            catch (XML_RPC2_FaultException $e) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    "Trac fetch failure: %d: %s\n",
                    $e->getFaultCode(),
                    $e->getFaultString()
                  )
                );
            }

            try {
                printf(
                  "Updating Trac ticket #%d, status: %s\n",
                  $ticketId,
                  $newStatus
                );

                $ticket->update(
                  $ticketId,
                  $message,
                  array(
                    'status'     => $newStatus,
                    'resolution' => $resolution
                  )
                );
            }

            catch (XML_RPC2_FaultException $e) {
                throw new PHPUnit_Framework_Exception(
                  sprintf(
                    "Trac update failure: %d: %s\n",
                    $e->getFaultCode(),
                    $e->getFaultString()
                  )
                );
            }

            PHPUnit_Util_Filesystem::collectEndAndAddToBlacklist();
        } else {
            throw new PHPUnit_Framework_Exception('XML_RPC2 is not available.');
        }
    }
}
?>