<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Graham Christensen <graham@grahamc.com>
 * @author     Sean Coates <sean@caedmon.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2011 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.4
 */

/**
 * A ticket listener that interacts with Trac.
 *
 * <code>
 * <phpunit>
 *  <listeners>
 *   <!-- You may need to update the path to the TicketListener -->
 *   <listener class="PHPUnit_Extensions_TicketListener_Trac"
 *             file="/usr/lib/php/PHPUnit/Extensions/TicketListener/Trac.php">
 *    <arguments>
 *     <!-- A user and their password. This user must have XML_RPC permissions -->
 *     <string>trac_username</string>
 *     <string>trac_password</string>
 *     <!-- The URL for your XML-RPC endpoint. -->
 *     <!-- For example, if trac is at 127.0.0.1/trac: -->
 *     <string>127.0.0.1/trac/login/xmlrpc</string>
 *    </arguments>
 *   </listener>
 *  </listeners>
 * </phpunit>
 * </code>
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Graham Christensen <graham@grahamc.com>
 * @author     Sean Coates <sean@caedmon.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2011 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.4
 */
class PHPUnit_Extensions_TicketListener_Trac extends PHPUnit_Extensions_TicketListener
{
    protected $username;
    protected $password;
    protected $hostpath;
    protected $scheme;
    private $printTicketStateChanges;

    /**
     * Constructor
     *
     * @param string $username Trac-XMLRPC username
     * @param string $password Trac-XMLRPC password
     * @param string $hostpath Trac-XMLRPC Host+Path (e.g. example.com/trac/login/xmlrpc)
     * @param string $scheme Trac scheme (http or https)
     * @param bool   $printTicketStateChanges To display changes or not
     */
    public function __construct($username, $password, $hostpath, $scheme = 'http', $printTicketStateChanges = FALSE)
    {
        $this->username                = $username;
        $this->password                = $password;
        $this->hostpath                = $hostpath;
        $this->scheme                  = $scheme;
        $this->printTicketStateChanges = $printTicketStateChanges;
    }

    /**
     * Get the status of a ticket message
     *
     * @param  integer $ticketId The ticket ID
     * @return array('status' => $status) ($status = new|closed|unknown_ticket)
     */
    public function getTicketInfo($ticketId = NULL)
    {
        if (!is_numeric($ticketId)) {
            return array('status' => 'invalid_ticket_id');
        }

        try {
            $info = $this->getClient()->get($ticketId);

            switch ($info[3]['status']) {
                case 'closed': {
                    return array('status' => 'closed');
                }
                break;

                case 'new':
                case 'reopened': {
                    return array('status' => 'new');
                }
                break;

                default: {
                    return array('status' => 'unknown_ticket');
                }
            }
        }

        catch (Exception $e) {
            return array('status' => 'unknown_ticket');
        }
    }

    /**
     * Update a ticket with a new status
     *
     * @param string $ticketId   The ticket number of the ticket under test (TUT).
     * @param string $statusToBe The status of the TUT after running the associated test.
     * @param string $message    The additional message for the TUT.
     * @param string $resolution The resolution for the TUT.
     */
    protected function updateTicket($ticketId, $statusToBe, $message, $resolution)
    {
        $change = array('status' => $statusToBe, 'resolution' => $resolution);

        $this->getClient()->update((int)$ticketId, $message, $change);

        if ($this->printTicketStateChanges) {
            printf(
              "\nUpdating Trac issue #%d, status: %s\n", $ticketId, $statusToBe
            );
        }
    }

    /**
     * Get a Trac XML_RPC2 client
     *
     * @return XML_RPC2_Client
     */
    protected function getClient()
    {
        if (!PHPUnit_Util_Filesystem::fileExistsInIncludePath('XML/RPC2/Client.php')) {
            throw new PHPUnit_Framework_Exception('PEAR/XML_RPC2 is not available.');
        }

        require_once 'XML/RPC2/Client.php';

        $url = sprintf(
          '%s://%s:%s@%s',
          $this->scheme,
          $this->username,
          $this->password,
          $this->hostpath
        );

        return XML_RPC2_Client::create($url, array('prefix' => 'ticket.'));
    }
}
