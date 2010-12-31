<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @author     Raphael Stolt <raphael.stolt@gmail.com>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.0
 */

/**
 * A ticket listener that interacts with the GitHub issue API.
 *
 * @package    PHPUnit
 * @subpackage Extensions_TicketListener
 * @author     Raphael Stolt <raphael.stolt@gmail.com>
 * @copyright  2002-2011 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.5.0
 */
class PHPUnit_Extensions_TicketListener_GitHub extends PHPUnit_Extensions_TicketListener
{
    const STATUS_CLOSE  = 'closed';
    const STATUS_REOPEN = 'reopened';

    private $username;
    private $apiToken;
    private $repository;
    private $apiPath = 'http://github.com/api/v2/json/issues';
    private $printTicketStateChanges;

    /**
     * @param string $username   The username associated with the GitHub account.
     * @param string $apiToken   The API token associated with the GitHub account.
     * @param string $repository The repository of the system under test (SUT) on GitHub.
     * @param string $printTicketChanges Boolean flag to print the ticket state
     * changes in the test result.
     * @throws RuntimeException
     */
    public function __construct($username, $apiToken, $repository, $printTicketStateChanges = FALSE)
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('ext/curl is not available');
        }

        if (!extension_loaded('json')) {
            throw new RuntimeException('ext/json is not available');
        }

        $this->username                = $username;
        $this->apiToken                = $apiToken;
        $this->repository              = $repository;
        $this->printTicketStateChanges = $printTicketStateChanges;
    }

    /**
     * @param  integer $ticketId
     * @return string
     * @throws RuntimeException
     */
    public function getTicketInfo($ticketId = NULL)
    {
        if (!is_numeric($ticketId)) {
            return array('status' => 'invalid_ticket_id');
        }

        $ticket = $this->callGitHub(
          $this->apiPath . '/show/' . $this->username . '/' .
          $this->repository . '/' . $ticketId,
          TRUE
        );

        if ($ticket['state'] === 'open') {
            return array('status' => 'new');
        }

        if ($ticket['state'] === 'closed') {
            return array('status' => 'closed');
        }

        if ($ticket['state'] === 'unknown_ticket') {
            return array('status' => 'unknown_ticket');
        }
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
        $acceptedResponseIssueStates = array('open', 'closed');

        if ($statusToBe === self::STATUS_CLOSE) {
            $apiEndpoint = $this->apiPath . '/close/' .
                           $this->username . '/' . $this->repository . '/' .
                           $ticketId;
        }

        else if ($statusToBe === self::STATUS_REOPEN) {
            $apiEndpoint = $this->apiPath . '/reopen/' .
                           $this->username . '/' . $this->repository . '/' .
                           $ticketId;
        }

        if (isset($apiEndpoint)) {
            $ticket = $this->callGitHub($apiEndpoint);

            if (!in_array($ticket['state'], $acceptedResponseIssueStates)) {
                throw new RuntimeException(
                  'Received an unaccepted issue state from the GitHub Api'
                );
            }

            if ($this->printTicketStateChanges) {
                printf(
                  "\nUpdating GitHub issue #%d, status: %s\n",
                  $ticketId,
                  $statusToBe
                );
            }
        }
    }

    /**
     * @param string  $apiEndpoint API endpoint to call against the GitHub issue API.
     * @param boolean $isShowMethodCall Show method of the GitHub issue API is called?
     * @return array
     * @throws RuntimeException
     */
    private function callGitHub($apiEndpoint, $isShowMethodCall = FALSE)
    {
        $curlHandle = curl_init();

        curl_setopt($curlHandle, CURLOPT_URL, $apiEndpoint);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, TRUE);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curlHandle, CURLOPT_HTTPPROXYTUNNEL, TRUE);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, __CLASS__);
        curl_setopt(
          $curlHandle,
          CURLOPT_POSTFIELDS,
          'login=' . $this->username . '&token=' . $this->apiToken
        );

        $response = curl_exec($curlHandle);

        if (!$response && $isShowMethodCall) {
            return array('state' => 'unknown_ticket');
        }

        if (!$response) {
            throw new RuntimeException(curl_error($curlHandle));
        }

        curl_close($curlHandle);

        $issue = (array)json_decode($response);

        return (array)$issue['issue'];
    }
}
