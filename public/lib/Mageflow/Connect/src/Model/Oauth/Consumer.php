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
class Consumer extends AbstractModel
{

    protected $_key;
    protected $_secret;

    /**
     * Class constructor
     *
     * @return Consumer
     */
    public function __construct($key, $consumerSecret)
    {
        $this->setKey($key);
        $this->setSecret($consumerSecret);
        return parent::__construct();
    }

}
