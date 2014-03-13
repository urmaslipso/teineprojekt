<?php

/**
 * PostRequest
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

use Mageflow\Connect\Model\Api\AbstractRequest;

/**
 * PostRequest
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
class PostRequest extends AbstractRequest
{

    private $instance_key;
    private $username;
    private $items = array();
    /**
     * Class constructor
     *
     * @return PostRequest
     */
    public function __construct()
    {
        return $this;
    }

    //put your code here
}
