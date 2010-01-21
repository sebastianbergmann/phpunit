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
 * @author     Raphael Stolt <raphael.stolt@gmail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.4.8
 */
require_once('PHPUnit/Extensions/TicketListener.php');
PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A ticket listener that interacts with the GitHub issue API.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Raphael Stolt <raphael.stolt@gmail.com>
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.4.8
 */
class PHPUnit_Extensions_TicketListener_GitHub extends 
    PHPUnit_Extensions_TicketListener
{
    const STATUS_CLOSE = 'closed';
    const STATUS_REOPEN = 'reopened';
    
    private $_username = null;
    private $_apiToken = null;
    private $_repository = null;
    private $_apiPath = null;
    private $_printTicketStateChanges = false;
    
    /**
     * @param string $username   The username associated with the GitHub account.
     * @param string $apiToken   The API token associated with the GitHub account.
     * @param string $repository The repository of the system under test (SUT) on GitHub.
     * @param string $printTicketChanges Boolean flag to print the ticket state 
     * changes in the test result.
     * @throws RuntimeException
     */
    public function __construct($username, $apiToken, $repository, 
        $printTicketStateChanges = false)
    {
        if ($this->_isCurlAvailable() === false) {
            throw new RuntimeException('The dependent curl extension is not available');
        }
        if ($this->_isJsonAvailable() === false) {
            throw new RuntimeException('The dependent json extension is not available');
        }
        $this->_username = $username;
        $this->_apiToken = $apiToken;
        $this->_repository = $repository;
        $this->_apiPath = 'http://github.com/api/v2/json/issues';
        $this->_printTicketStateChanges = $printTicketStateChanges;
    }
    
    /**
     * @param  integer $ticketId 
     * @return string
     * @throws PHPUnit_Framework_Exception
     */
    public function getTicketInfo($ticketId = null) 
    {
        if (!ctype_digit($ticketId)) {
            return $ticketInfo = array('status' => 'invalid_ticket_id');
        }                
        $ticketInfo = array();
        
        $apiEndpoint = "{$this->_apiPath}/show/{$this->_username}/"
            . "{$this->_repository}/{$ticketId}";
            
        $issueProperties = $this->_callGitHubIssueApiWithEnpoint($apiEndpoint, true);

        if ($issueProperties['state'] === 'open') {
            return $ticketInfo = array('status' => 'new');
        } elseif ($issueProperties['state'] === 'closed') {
            return $ticketInfo = array('status' => 'closed');
        } elseif ($issueProperties['state'] === 'unknown_ticket') {
            return $ticketInfo = array('status' => $issueProperties['state']);
        }
    }

    /**
     * @param string $ticketId   The ticket number of the ticket under test (TUT).
     * @param string $statusToBe The status of the TUT after running the associated test.
     * @param string $message    The additional message for the TUT.
     * @param string $resolution The resolution for the TUT.
     * @throws PHPUnit_Framework_Exception
     */
    protected function updateTicket($ticketId, $statusToBe, $message, $resolution)
    {
        $apiEndpoint = null;
        $acceptedResponseIssueStates = array('open', 'closed');
        
        if ($statusToBe === self::STATUS_CLOSE) {
            $apiEndpoint = "{$this->_apiPath}/close/{$this->_username}/"
                . "{$this->_repository}/{$ticketId}";
        } elseif ($statusToBe === self::STATUS_REOPEN) {
            $apiEndpoint = "{$this->_apiPath}/reopen/{$this->_username}/"
                . "{$this->_repository}/{$ticketId}";
        }
        if (!is_null($apiEndpoint)) {
            $issueProperties = $this->_callGitHubIssueApiWithEnpoint($apiEndpoint);
            if (!in_array($issueProperties['state'], $acceptedResponseIssueStates)) {
                throw new PHPUnit_Framework_Exception(
                    'Recieved an unaccepted issue state from the GitHub Api');
            }
            if ($this->_printTicketStateChanges) {
                printf("\nUpdating GitHub issue #%d, status: %s\n", $ticketId, 
                    $statusToBe);
            }
        }
    }
    /**
     * @return boolean 
     */
    private function _isCurlAvailable()
    {
        return extension_loaded('curl');
    }
    /**
     * @return boolean 
     */
    private function _isJsonAvailable()
    {
        return extension_loaded('json');
    }
    /**
     * @param string  $apiEndpoint API endpoint to call against the GitHub issue API.
     * @param boolean $isShowMethodCall Show method of the GitHub issue API is called? 
     * @return array
     * @throws PHPUnit_Framework_Exception
     */
    private function _callGitHubIssueApiWithEnpoint($apiEndpoint, 
        $isShowMethodCall = false) 
    {
            $curlHandle = curl_init();

            curl_setopt($curlHandle, CURLOPT_URL, $apiEndpoint);
            curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curlHandle, CURLOPT_FAILONERROR, true);
            curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_HTTPPROXYTUNNEL, true);
            curl_setopt($curlHandle, CURLOPT_USERAGENT, __CLASS__);  
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS,
                "login={$this->_username}&token={$this->_apiToken}");

            $response = curl_exec($curlHandle);
            
            // Unknown tickets throw a 403 error
            if (!$response && $isGetTicketInfoCall) {
                return array('state' => 'unknown_ticket');
            }

            if (!$response) {
                $curlErrorMessage = curl_error($curlHandle);
                $exceptionMessage = "A failure occured while talking to the "
                    . "GitHub issue Api. {$curlErrorMessage}.";
                throw new PHPUnit_Framework_Exception($exceptionMessage);
            }
            $issue = (array) json_decode($response);
            $issueProperties = (array) $issue['issue'];
            curl_close($curlHandle);
            return $issueProperties;
    }
}