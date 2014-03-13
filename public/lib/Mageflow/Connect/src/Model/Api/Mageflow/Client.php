<?php

/**
 * Client
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */

namespace Mageflow\Connect\Model\Api\Mageflow;

use Mageflow\Connect\Helper\Logger as Logger;
use Mageflow\Connect\Model\Api\AbstractClient;
use Mageflow\Connect\Model\Oauth;

/**
 * Client
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 *
 */
class Client extends AbstractClient
{

    const REQUEST_TIMEOUT = 200;
    protected $_apiUrl;

    public function __construct(\stdClass $configuration = null)
    {
        parent::__construct($configuration);
    }

    /**
     * Returns MageFlow API URL
     *
     * @return string
     */
    public function getApiUrl()
    {
        if (is_null($this->apiUrl)) {
            $this->apiUrl = \Mage::app()->getStore()->getConfig(\Mageflow_Connect_Model_System_Config::API_URL);
        }
        return $this->apiUrl;
    }

    /**
     * Get method makes a get request to specified resource
     * with API base url prepended
     *
     * @see self::post()
     *
     * @param string $resource
     * @param        array /stdClass $params
     *
     * @return mixed
     */
    public function get($resource, $params = array())
    {
        Logger::debug($resource);
        Logger::debug($params);

        //create token
        $token = new Oauth\Token(
            $this->getToken(), $this->getTokenSecret()
        );

        Logger::debug($token);

        //create consumer
        $consumer = new Oauth\Consumer(
            $this->getConsumerKey(), $this->getConsumerSecret()
        );

        Logger::debug($consumer);

        //get signature method
        $sigMethod = new Oauth\Signature\HMACSHA1();

        $resource = '/' . ltrim($resource, '/');
        $url = rtrim(\Mage::getStoreConfig('mageflow_connect/advanced/api_url'), '/') . $resource;
        Logger::debug($url);
        $method = 'GET';

        if (isset($params['id']) && $params['id']) {
            $url .= '/' . $params['id'];
            unset($params['id']);
        }

        if (isset($this->_company) && $this->_company) {
            $url .= '/company/' . $this->_company;
        }

        if (isset($params['project']) && $params['project']) {
            $url .= '/project/' . $params['project'];
            unset($params['project']);
        }

        //create request
        $request = new Oauth\Request($method, $url, $params);

        //sign request
        $request->signRequest($sigMethod, $consumer, $token);

        //make http query
        $response = $this->makeHttpRequest($request);
        return $response;
    }

    /**
     * Post method makes a post request to specified resource
     * with API base url prepended
     *
     * @param string $resource
     * @param        array /stdClass $data
     *
     * @return mixed
     */
    public function post($resource, $data = array())
    {
        Logger::debug($data);

        //create token
        $token = new Oauth\Token(
            $this->getToken(), $this->getTokenSecret()
        );

        //create consumer
        $consumer = new Oauth\Consumer(
            $this->getConsumerKey(), $this->getConsumerSecret()
        );

        //get signature method
        $sigMethod = new Oauth\Signature\HMACSHA1();

        $resource = '/' . ltrim($resource, '/');
        $url = rtrim(\Mage::getStoreConfig('mageflow_connect/advanced/api_url'), '/') . $resource;
        $method = 'POST';

        //create request
        $request = new Oauth\Request($method, $url, $data);

        //sign request
        $request->signRequest($sigMethod, $consumer, $token);

        //make http query
        $response = $this->makeHttpRequest($request);

        Logger::debug($response);
        return $response;
    }

    /**
     * Wrapper to make the real HTTP request. Inside here
     * we can use cURL or whatever instaed of direct stream
     *
     * @param \Mageflow\Connect\Model\Oauth\Request $request
     *
     * @return mixed
     */
    private function makeHttpRequest(Oauth\Request $request)
    {
        $contextParams = array(
            'http' => array(
                'header'  => $request->getHeaders(), // . "\r\nContent-Type: application/json\r\n",
                'method'  => $request->getHttpMethod(),
                'content' => http_build_query($request->getRequestData()),
                'timeout' => Client::REQUEST_TIMEOUT,
            )
        );
        Logger::debug($request->getHttpUrl());
        Logger::debug($contextParams);

        $context = stream_context_create($contextParams);

        try {
            $response = @file_get_contents($request->getHttpUrl(), false, $context);
            Logger::debug($response);
//            Logger::debug(@debug_backtrace());
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
        }

        return $response;
    }

    /**
     * Implements put method of MageFlow API URL
     *
     * @param       $resource
     * @param array $data
     *
     * @return mixed
     */
    public function put($resource, $data = array())
    {
        Logger::debug($data);

        //create token
        $token = new Oauth\Token(
            $this->getToken(), $this->getTokenSecret()
        );
        Logger::debug($token);
        //create consumer
        $consumer = new Oauth\Consumer(
            $this->getConsumerKey(), $this->getConsumerSecret()
        );
        Logger::debug($consumer);
        //get signature method
        $sigMethod = new Oauth\Signature\HMACSHA1();

        $resource = '/' . ltrim($resource, '/');
        $url = rtrim(\Mage::getStoreConfig('mageflow_connect/advanced/api_url'), '/') . $resource;
        $method = 'PUT';

        if (isset($this->_company) && $this->_company) {
            $data['company'] = $this->_company;
        }

        //create request
        $request = new Oauth\Request($method, $url, $data);

        //sign request
        $request->signRequest($sigMethod, $consumer, $token);

        //make http query
        $response = $this->makeHttpRequest($request);

        Logger::debug($response);
        return $response;

    }

}