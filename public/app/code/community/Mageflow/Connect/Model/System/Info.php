<?php

/**
 * Info
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
 * Info
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_System_Info extends Varien_Object
{

    const PERFORMANCE_HISTORY_DISPLAY_ITEMS = 10;

    /**
     * Class constructor
     *
     * @return Info
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Returns array with request/memory/cpu/sessions history
     *
     * @return array
     */
    public function getPerformanceHistory()
    {
        $memoryUsageModelCollection = Mage::getModel(
            'mageflow_connect/system_info_performance'
        )
            ->getCollection()->setPageSize(
                self::PERFORMANCE_HISTORY_DISPLAY_ITEMS
            );
        $memoryUsageModelCollection->addOrder('created_at', 'DESC');
        $out = $memoryUsageModelCollection->toArray();
        return $out['items'];
    }

}
