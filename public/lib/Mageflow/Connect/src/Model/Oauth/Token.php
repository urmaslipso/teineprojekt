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

/**
 * Token
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
class Token extends AbstractModel
{

    protected $_token;
    protected $_secret;

    /**
     * Class constructor
     *
     * @return Token
     */
    public function __construct($token, $secret)
    {
        $this->setToken($token);
        $this->setSecret($secret);
        return parent::__construct();
    }

}
