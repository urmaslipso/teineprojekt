<?php

/**
 * Token
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */

namespace Mageflow\Connect\Model\Oauth;

use Mageflow\Connect\Model\AbstractModel;
use Mageflow\Connect\Model\Oauth\Util;
use Mageflow\Connect\Model\Oauth\Interfaces\SignatureBuilder;

/**
 * Token
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 *
 * @method string getHttpMethod()
 * @method string getHttpUrl()
 * @method string getVersion()
 * @method array getParameters()
 * @method setParameters(array $parameters)
 * @method string getBaseString()
 * @method setBaseString($string)
 * @method array getRequestData()
 */
class Request extends AbstractModel
{

    protected $_parameters = array();
    protected $_httpMethod;
    protected $_httpUrl;
    protected $_requestData;
    // for debug purposes
    public $_base_string;
    protected $_version = '1.0';
    public $POST_INPUT = 'php://input';

    /**
     * Class constructor
     *
     * @return Token
     */
    public function __construct($httpMethod, $httpUrl, $parameters = array())
    {
        $parameters = array_merge(
            Util::parseParameters(
                parse_url($httpUrl, PHP_URL_QUERY)
            ), $parameters
        );
        $this->setRequestData($parameters);
        $this->setParameters($parameters);
        $this->setHttpMethod($httpMethod);
        $this->setHttpUrl($httpUrl);
        return parent::__construct();
    }

    private function prepareParameters(Consumer $consumer, Token $token)
    {
        $defaults = array("oauth_version" => $this->getVersion(),
            "oauth_nonce" => $this->generateNonce(),
            "oauth_timestamp" => $this->generateTimestamp(),
            "oauth_consumer_key" => $consumer->getKey());
        if ( $token ) $defaults['oauth_token'] = $token->getToken();

        $this->setParameters(array_merge($defaults, $this->getParameters()));
    }

    /**
     * Signs current request
     *
     * @param \Mageflow\Connect\Model\Oauth\Interfaces\SignatureBuilder $signatureMethod
     * @param \Mageflow\Connect\Model\Oauth\Consumer $consumer
     * @param \Mageflow\Connect\Model\Oauth\Token $token
     */
    public function signRequest(Signature\SignatureBuilder $signatureMethod,
        Consumer $consumer, Token $token)
    {
        $this->prepareParameters($consumer, $token);
        $this->setParameter(
            "oauth_signature_method", $signatureMethod->getName()
        );
        $signature = $signatureMethod->buildSignature($this, $consumer, $token);
        $this->setParameter("oauth_signature", $signature);
        return $this;
    }

    /**
     * Creates and returns signature for current request
     *
     * @param \Mageflow\Connect\Model\Oauth\Interfaces\SignatureBuilder $signatureMethod
     * @param \Mageflow\Connect\Model\Oauth\Consumer $consumer
     * @param \Mageflow\Connect\Model\Oauth\Token $token
     * @return string
     */
    public function getSignature(SignatureBuilder $signatureMethod,
        Consumer $consumer, Token $token)
    {
        $this->prepareParameters($consumer, $token);
        $signature = $signatureMethod->buildSignature($this, $consumer, $token);
        return $signature;
    }

    /**
     * util function: current timestamp
     */
    private function generateTimestamp()
    {
        return time();
    }

    /**
     * util function: current nonce
     */
    private function generateNonce()
    {
        $mt = microtime();
        $rand = mt_rand(1, time());

        return md5($mt . $rand); // md5s look nicer than numbers
    }

    /**
     * Sets parameter
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
        $this->_parameters[$name] = $value;
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function getSignatureBaseString()
    {
        $parts = array(
            $this->getNormalizedHttpMethod(),
            $this->getNormalizedHttpUrl(),
            $this->getSignableParameters()
        );

        $parts = Util::urlencodeRFC3986($parts);

        return implode('&', $parts);
    }

    /**
     * just uppercases the http method
     */
    public function getNormalizedHttpMethod()
    {
        return strtoupper($this->getHttpMethod());
    }

    /**
     * parses the url and rebuilds it to be
     * scheme://host/path
     */
    public function getNormalizedHttpUrl()
    {
        $parts = parse_url($this->_httpUrl);

        $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
        $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
        $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
        $path = (isset($parts['path'])) ? $parts['path'] : '';

        if ( ($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port != '80') )
        {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }

    /**
     * builds a url usable for a GET request
     */
    public function createGetUrl()
    {
        $post_data = $this->createPostData();
        $out = $this->getNormalizedHttpUrl();
        if ( $post_data )
        {
            $out .= '?' . $post_data;
        }
        return $out;
    }

    /**
     * builds the data one would send in a POST request
     */
    public function createPostData()
    {
        return Util::buildHttpQuery($this->getParameters());
    }

    /**
     * builds the Authorization: header
     */
    public function getHeaders($realm = null)
    {
        $first = true;
        if ( $realm )
        {
            $out = 'Authorization: OAuth realm="' . Util::urlencodeRFC3986($realm) . '"';
            $first = false;
        } else
        {
            $out = 'Authorization: OAuth';
        }

        foreach ( $this->getParameters() as $key => $value )
        {
            if ( substr($key, 0, 5) != "oauth" ) continue;
            if ( is_array($value) )
            {
                throw new OAuthException('Arrays not supported in headers');
            }
            $out .= ($first) ? ' ' : ',';
            $out .= Util::urlencodeRFC3986($key) .
                '="' .
                Util::urlencodeRFC3986($value) .
                '"';
            $first = false;
        }
        return $out;
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     * @return string
     */
    public function getSignableParameters()
    {
        // Grab all parameters
        $params = $this->getParameters();

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if ( isset($params['oauth_signature']) )
        {
            unset($params['oauth_signature']);
        }

        return Util::buildHttpQuery($params);
    }

}
