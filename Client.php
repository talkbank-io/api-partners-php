<?php

namespace TalkBank\ApiPartners;

use GuzzleHttp\Client as GuzzleClient;

/**
 * API for partners
 *   $client = new Client('http://localhost/api/partners/000a.../', '000a...', 'a00000....');
 *
 * @package TB\ApiPartners
 * @author  ploginoff
 */
class Client
{
    /**
     * @var GuzzleClient
     */
    protected $guzzle;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $partnerId;

    public function __construct(string $host, string $partnerId, string $token)
    {
        $this->token = $token;
        $this->partnerId = $partnerId;
        $this->guzzle = new GuzzleClient(['base_uri' => $host,]);
    }

    /**
     * Get callbacks
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCallback() : array
    {
        return $this->exec('GET', 'callbacks');
    }

    /**
     * Set a callback
     *
     * @param array $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setCallback(array $params) : array
    {
        return $this->exec('PUT', 'callbacks', $params);
    }

    /**
     * Add a client
     *
     * @param array $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addClient(array $params) : array
    {
        return $this->exec('POST', 'clients', $params);
    }

    /**
     * Edit a client
     *
     * @param  string $clientId
     * @param  array  $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editClient(string $clientId, array $params) : array
    {
        return $this->exec('POST', 'clients/' . $clientId . '/edit', $params);
    }

    /**
     * Get client's status
     *
     * @param string $clientId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClientStatus(string $clientId) : array
    {
        return $this->exec('GET', 'clients/' . $clientId);
    }

    /**
     * Add a contract to some client
     *
     * @param string $clientId
     * @param array $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addContracts(string $clientId, array $params) : array
    {
        return $this->exec('POST', 'clients/' . $clientId . '/contracts', $params);
    }

    /**
     * Get contracts list from some client
     *
     * @param string $clientId
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getContracts(string $clientId) : array
    {
        return $this->exec('GET', 'clients/' . $clientId . '/contracts');
    }


    /**
     * Get transactions for some card
     *
     * @param string $barcode
     * @param string $fromDate
     * @param string $toDate
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTransactions(string $barcode, string $fromDate, string $toDate) : array
    {
        $params = http_build_query(['fromDate' => $fromDate, 'toDate' => $toDate]);
        return $this->exec('GET', 'cards/' . $barcode . '/transactions?' . $params);
    }

    /**
     * Get card's details
     *
     * @param string $barcode
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDetails(string $barcode) : array
    {
        return $this->exec('GET', 'cards/' . $barcode . '/details');
    }

    /**
     * Get a report (pdf)
     *
     * @param string $barcode
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getReportStatus(string $barcode) : array
    {
        return $this->exec('GET', 'cards/' . $barcode . '/report_status');
    }

    /**
     * Get a report with transactions (pdf)
     *
     * @param string $barcode
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getReportTransactions(string $barcode) : array
    {
        return $this->exec('GET', 'cards/' . $barcode . '/report_transactions');
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $params
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function exec(string $method, string $path, array $params = [])
    {
        $response = $this->guzzle->request($method, $path, [
            'json'      => $params,
            'debug'     => true,
            'headers'   => [
                'Authorization' => 'signature="'. $this->signature($params) .'"',
            ]
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $id
     * @param string $token
     * @param array|null $params
     * @return string
     */
    protected function signature(?array $params = null) : string
    {
        return hash('sha256', $this->partnerId . $this->token . ($params ? json_encode($params) : ''));
    }

    /**
     * @param string $clientId
     * @param array|null $params
     * @example
     *      $params = [
     *          'fromBarcode' => '...',
     *          'toBarcode' => '...',
     *          'amount' => 10.1,
     *          'isExtended' => true,
     *          'extendedOptions' => [
     *              'has_comment' => '',
     *              'description' => '',
     *              'comment_required' => false,
     *              'comment_placeholder' => ''
     *          ],
     *          'smsOptions' => [
     *              'phone' => '+7...',
     *              'comment' => 'Ваша ссылка: {{link}}'
     *          ]
     *      ]
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function extendedPaymentLink(string $clientId, array $params = null)
    {
        return $this->exec('POST', 'extra/' . $clientId . '/extendedPaymentLink', $params);
    }

    /**
     * @param string $barcode
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendSecurityCode(string $barcode)
    {
        return $this->exec('GET', 'cards/' . $barcode . '/send/security/code');
    }
}
