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

namespace Mageflow\Connect\Model\Api;

use Mageflow\Connect\Model\AbstractModel;

/**
 * Client
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 *
 * @method string getToken()
 * @method string getTokenSecret()
 * @method string getConsumerKey()
 * @method string getConsumerSecret()
 */
abstract class AbstractClient extends AbstractModel
{

    protected $_apiUrl = null;
    protected $_token;
    protected $_tokenSecret;
    protected $_consumerKey;
    protected $_consumerSecret;
    private $_logger;

    /**
     * Class constructor
     *
     * @return Client
     */
    public function __construct(\stdClass $configuration = null)
    {
        if ( !is_null($configuration) )
        {
            foreach ( $configuration as $key => $value )
            {
                $this->$key = $value;
            }
        }
        return $this;
    }

    protected function sendRequest($resource = 'package')
    {
        $data = array(
            'consumerkey' => 'test'
        );

        $string = implode('&', $data);
        $signature = hash_hmac('sha256', $string, 'test');
        $string .= '&signature' . $signature;
        \Helper\Logger::debug(print_r($string, true), __METHOD__, __LINE__);
        $httpClient = new \Zend_Http_Client($this->escApiUrl . $resource);
        $httpClient->setHeaders('Authorization', 'token_blaah');

        $response = $httpClient->request();
        \Helper\Logger::debug(print_r($response->getBody(), true), __METHOD__,
            __LINE__);
    }

    /**
     *
     * @return \Mageflow\Connect\Helper\Logger
     */
    protected function getLogger()
    {
        if ( is_null($this->_logger) )
        {
            $this->_logger = new \Mageflow\Connect\Helper\Logger();
        }
        return $this->_logger;
    }

}
