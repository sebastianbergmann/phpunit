<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @package    PHPUnit
 * @subpackage Extensions_TicketListener
 * @author     Jan Sorgalla <jsorgalla@googlemail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.0
 */

/**
 * A ticket listener that interacts with the Fogbugz issue API.
 *
 * @package    PHPUnit
 * @subpackage Extensions_TicketListener
 * @author     Jan Sorgalla <jsorgalla@googlemail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.0
 * @see        http://fogbugz.stackexchange.com/fogbugz-xml-api
 */
class PHPUnit_Extensions_TicketListener_Fogbugz extends PHPUnit_Extensions_TicketListener
{
    private $email;
    private $password;

    private $statusClosed;
    private $statusReopened;

    private $printTicketStateChanges;

    private $authUrl    = '';
    private $apiBaseUrl = '';
    private $authToken;

    /**
     * Constructor, which often takes its parameters from an XML file of config.
     * 
     * Note: This is how the logon takes place.
     * 
     * api.asp?cmd=logon&email=xxx@example.com&password=BigMac
     * 
     * @param string $email          The email associated with the Fogbugz account.
     * @param string $password       The password associated with the Fogbugz account.
     * @param string $siteApiUrl     The URL to Fogbugz installation, for
     * instance fogbugz.company.com/api.asp
     * @param string $printTicketChanges Boolean flag to print the ticket state changes in the test result.
     * @param string $statusClosed   The status name of the closed state.
     * @param string $statusReopened The status name of the reopened state.
     * @throws RuntimeException
     */
    public function __construct($email, $password, $siteApiUrl, $printTicketStateChanges = FALSE, $statusClosed = 'Fixed', $statusReopened = 'Started')
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('ext/curl is not available');
        }

        $this->email                   = $email;
        $this->password                = $password;
        $this->statusClosed            = $statusClosed;
        $this->statusReopened          = $statusReopened;
        $this->printTicketStateChanges = $printTicketStateChanges;
        $this->apiBaseUrl              = $siteApiUrl . '?';
        $this->authUrl                 = sprintf($this->apiBaseUrl . 'cmd=logon&email=%s&password=%s', $email, $password);
    }

    /**
     * @param  integer $ticketId
     * @return array
     * @throws RuntimeException
     */
    public function getTicketInfo($ticketId = NULL)
    {
        if (!is_numeric($ticketId)) {
            return array('status' => 'invalid_ticket_id');
        }
		// cmd=search&q=4239&cols=id&token=4nv48nf2ss076kiad4nbf6e7phtl23
		
		$fields = array(
			'ixBug',
			'ixBugParent',
			'ixBugChildren',
			'tags',
			'fOpen',
			'sTitle',
			'sOriginalTitle ',
			'sLatestTextSummary',
			'ixBugEventLatestText',
			'ixProject',
			'sProject',
			'ixArea',
			'sArea',
			'ixGroup',
			'ixPersonAssignedTo',
			'sPersonAssignedTo',
			'sEmailAssignedTo',
			'ixPersonOpenedBy',
			'ixPersonResolvedBy',
			'ixPersonClosedBy',
			'ixPersonLastEditedBy',
			'ixStatus ',
			'sStatus',
			'ixPriority ',
			'sPriority',
			'ixFixFor',
			'sFixFor',
			'dtFixFor',
			'sVersion',
			'sComputer',
			'hrsOrigEst',
			'hrsCurrEst',
			'hrsElapsed',
			'c',
			'sCustomerEmail ',
			'ixMailbox',
			'ixCategory',
			'sCategory',
			'dtOpened',
			'dtResolved ',
			'dtClosed',
			'ixBugEventLatest',
			'dtLastUpdated',
			'fReplied',
			'fForwarded',
			'sTicket',
			'ixDiscussTopic',
			'dtDue',
			'sReleaseNotes',
			'ixBugEventLastView',
			'dtLastView',
			'ixRelatedBugs',
			'sScoutDescription',
			'sScoutMessage',
			'fScoutStopReporting',
			'fSubscribed');
		$fewFields = array('ixBug', 'sTitle', 'fOpen', 'ixStatus', 'sStatus');
		
        $url    = $this->apiBaseUrl . 'cmd=search&q=' . $ticketId . '&cols=' . implode(',', $fewFields) . '&token='.$this->getAuthToken();

        list($status, $response) = $this->callFogbugz($url);

        if ($status != 200 || !$response) {
            return array('state' => 'unknown_ticket');
        }

        $ticket = new SimpleXMLElement($response);
        $nCases = $ticket->xpath('//response/cases');
		var_dump($nCases);
		var_dump($response);
		
        if (count($nCases) < 1) {
            return array('state' => 'unknown_ticket');
        }
        $result = $ticket->xpath('//ixStatus');
        $state  = (string)$result[0];

        if ($state === '1') {
            return array('status' => 'new');
        }

        if ($state === 'closed') {
            return array('status' => 'closed');
        }

        return array('status' => $state);
    }

    /**
     * @param string $ticketId   The ticket number of the ticket under test (TUT).
     * @param string $statusToBe The status of the TUT after running the associated test.
     * @param string $message    The additional message for the TUT.
     * @param string $resolution The resolution for the TUT.
     * @throws RuntimeException
     */
    protected function updateTicket($ticketId, $statusToBe, $message, $resolution)
    {
        $url = $this->apiBaseUrl . '/' . $ticketId . '/comments/full';

        $header = array(
          'Authorization: GoogleLogin auth=' . $this->getAuthToken(),
          'Content-Type: application/atom+xml'
        );

        if ($statusToBe == 'closed') {
            $ticketStatus = $this->statusClosed;
        } else {
            $ticketStatus = $this->statusReopened;
        }

        list($author,) = explode('@', $this->email);

        $post = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<entry xmlns="http://www.w3.org/2005/Atom" ' .
                '       xmlns:issues="http://schemas.google.com/projecthosting/issues/2009">' .
                '  <content type="html">' . htmlspecialchars($message, ENT_COMPAT, 'UTF-8') . '</content>' .
                '  <author>' .
                '    <name>' . htmlspecialchars($author, ENT_COMPAT, 'UTF-8') . '</name>' .
                '  </author>' .
                '  <issues:updates>' .
                '    <issues:status>' . htmlspecialchars($ticketStatus, ENT_COMPAT, 'UTF-8') . '</issues:status>' .
                '  </issues:updates>' .
                '</entry>';

        list($status, $response) = $this->callFogbugz($url, $header, $post);

        if ($status != 201) {
            throw new RuntimeException('Updating Fogbugz issue failed with status code ' . $status);
        }

        if ($this->printTicketStateChanges) {
            printf(
                "\nUpdating Fogbugz issue #%d, status: %s\n",
                $ticketId,
                $statusToBe
            );
        }
    }

    /**
     * @return string The auth token
     * @throws RuntimeException
     */
    private function getAuthToken()
    {
        if (NULL !== $this->authToken) {
            return $this->authToken;
        }

        $header = array(
            'Content-Type: application/x-www-form-urlencoded',
        );

        list($status, $response) = $this->callFogbugz(
            $this->authUrl
        );
        if ($status != 200) {
            throw new RuntimeException('Fogbugz authentication failed');
        }
		$xml = new SimpleXMLElement($response);
		$tokenChunk = $xml->xpath('//response/token');
		$tokenString = (string)$tokenChunk[0];
		// TODO: Cut out the TOKEN of the resulting XML.
		// save it in the $this->authToken slot
        $this->authToken    = trim($tokenString);

        if (NULL === $this->authToken) {
            throw new RuntimeException('Could not detect auth token in response');
        }

        return $this->authToken;
    }

    /**
     * @param string  $url URL to call
     * @param array   $header Header
     * @param string  $post Post data
     * @return array
     */
    private function callFogbugz($url, array $header = NULL, $post = NULL)
    {
        $curlHandle = curl_init();
		
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, TRUE);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curlHandle, CURLOPT_HTTPPROXYTUNNEL, TRUE);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, __CLASS__);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);

        if (NULL !== $header) {
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $header);
        }

        if (NULL !== $post) {
            curl_setopt($curlHandle, CURLOPT_POST, TRUE);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $post);
        }

        $response = curl_exec($curlHandle);
        $status   = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);

        if (!$response) {
            throw new RuntimeException(curl_error($curlHandle));
        }

        curl_close($curlHandle);

        return array($status, $response);
    }
}
