<?php

/**
 * Session
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Session
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_System_Info_Session extends Varien_Object
{

    /**
     * Class constructor
     *
     * @return Session
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Returns number of active sessions
     *
     * @return int
     */
    public function getNumberOfActiveSessions()
    {
        $collection = Mage::getModel('log/visitor_online')
            ->prepare()
            ->getCollection();
        return $collection->count();
    }

}
